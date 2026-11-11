"""Shared black-box contract tests. Run only against a clean disposable database."""
import argparse
import json
import sys
import unittest
from urllib.request import Request, urlopen
from urllib.error import HTTPError

parser = argparse.ArgumentParser()
parser.add_argument('--base-url', default='http://localhost:8080')
args = parser.parse_args()
BASE = args.base_url.rstrip('/')
RULES = [{'label': x, 'percentage': p} for x, p in [('checking', 50), ('savings', 30), ('investment', 20)]]

class Acceptance(unittest.TestCase):
    def call(self, method, path, data=None, expected=200):
        body = None if data is None else json.dumps(data).encode()
        req = Request(BASE + path, data=body, method=method,
                      headers={'Content-Type': 'application/json'})
        try:
            with urlopen(req, timeout=10) as r:
                status, raw = r.status, r.read()
        except HTTPError as e:
            status, raw = e.code, e.read()
        self.assertEqual(status, expected, f'{method} {path}: {raw!r}')
        value = json.loads(raw)
        if expected >= 400:
            self.assertIsInstance(value.get('error'), str)
            self.assertTrue(value['error'])
        return value

    def income(self, cents):
        value = self.call('POST', '/api/incomes', {'amount_cents': cents, 'description': 'Test paycheck'}, 201)
        self.assertIsInstance(value['id'], int)
        self.assertEqual(value['amount_cents'], cents)
        return value['id']

    def rules(self, rules=RULES):
        value = self.call('PUT', '/api/allocation-rules', {'destinations': rules})
        self.assertEqual(value['destinations'], rules)

    def entries(self, value, amounts, labels=None):
        self.assertEqual([e['amount_cents'] for e in value['entries']], amounts)
        if labels is not None:
            self.assertEqual([e['label'] for e in value['entries']], labels)
        self.assertEqual(value['total_cents'], sum(amounts))

    def test_01_empty_history_and_missing_records(self):
        self.assertEqual(self.call('GET', '/api/transactions')['batches'], [])
        for path in ['/api/transactions/2147483647']:
            self.call('GET', path, expected=404)
        for path in ['/api/distributions/preview', '/api/transfers/simulate']:
            self.call('POST', path, {'income_id': 2147483647}, 404)
        for limit in ['0', '101', 'abc']:
            self.call('GET', '/api/transactions?limit=' + limit, expected=422)
        self.assertEqual(self.call('GET', '/api/transactions')['batches'], [])

    def test_02_missing_rules(self):
        income_id = self.income(100000)
        for path in ['/api/distributions/preview', '/api/transfers/simulate']:
            self.call('POST', path, {'income_id': income_id}, 422)
        self.assertEqual(self.call('GET', '/api/transactions')['batches'], [])

    def test_03_income_validation(self):
        for amount in [0, -100, 'abc', 10.5]:
            with self.subTest(amount=amount):
                self.call('POST', '/api/incomes', {'amount_cents': amount}, 422)
        self.call('POST', '/api/incomes', {}, 422)

    def test_04_rules_validation_and_preview(self):
        self.rules()
        income_id = self.income(100000)
        bad = [[], [{'label': '', 'percentage': 100}],
               [{'label': 'same', 'percentage': 50}, {'label': 'same', 'percentage': 50}]]
        for pct in [90, 110, -1, 101, 50.5, 'abc']:
            bad.append([{'label': 'checking', 'percentage': pct}])
        for rules in bad:
            with self.subTest(rules=rules):
                self.call('PUT', '/api/allocation-rules', {'destinations': rules}, 422)
                result = self.call('POST', '/api/distributions/preview', {'income_id': income_id})
                self.entries(result, [50000, 30000, 20000], ['checking','savings','investment'])
        self.assertEqual(self.call('GET', '/api/transactions')['batches'], [])

    def test_05_rounding(self):
        self.rules()
        result = self.call('POST', '/api/distributions/preview', {'income_id': self.income(10001)})
        self.entries(result, [5001, 3000, 2000])
        self.rules([{'label':'first','percentage':50}, {'label':'second','percentage':50}])
        result = self.call('POST', '/api/distributions/preview', {'income_id': self.income(1)})
        self.entries(result, [1, 0], ['first','second'])
        self.rules()

    def test_06_simulation_and_history(self):
        self.rules()
        income_id = self.income(100000)
        value = self.call('POST', '/api/transfers/simulate', {'income_id':income_id}, 201)
        self.assertIsInstance(value['batch_id'], int)
        self.assertEqual(value['income_id'], income_id)
        self.assertIs(value['simulated'], True)
        self.entries(value, [50000,30000,20000], ['checking','savings','investment'])
        before = self.call('GET', '/api/transactions')['batches']
        self.assertEqual(before, [value])
        self.assertEqual(self.call('GET', '/api/transactions/' + str(value['batch_id'])), value)
        self.call('POST', '/api/transfers/simulate', {'income_id':income_id}, 409)
        self.assertEqual(self.call('GET', '/api/transactions')['batches'], before)

if __name__ == '__main__':
    result = unittest.TextTestRunner(verbosity=2).run(unittest.defaultTestLoader.loadTestsFromTestCase(Acceptance))
    sys.exit(0 if result.wasSuccessful() else 1)
