# SARAKI — UAT Bug Log

One row per defect. Severity: **Critical** (blocks purchase/booking or loses data), **High** (major flow broken, workaround exists), **Medium** (annoying, cosmetic-ish), **Low** (polish).

| ID | Date | Step | Severity | Description | Steps to reproduce | Expected | Actual | Status | Fixed in |
|---|---|---|---|---|---|---|---|---|---|
| BUG-001 | | | | | | | | Open | |

## Triage rules

- **Critical/High** must be fixed and re-tested before G6 sign-off.
- **Medium/Low** may be deferred to v1.1 with client agreement — record the decision here.
- Re-test every fixed bug against the same step ID before closing it.

## Deferred / known limitations

| Item | Reason | Target |
|---|---|---|
| Real Safari on macOS/iOS | Cannot run on the Windows dev machine; WebKit engine approximated in Task 6.1 | Before G6 if a Mac is available, else first week post-launch |
| Paystack live keys | Test keys pending from client (D-xx); UAT step B5/B6 run on staging with test mode | Phase 7 deployment |
| Off-site backup copy | `backup:run` writes locally; off-site sync is a hosting decision | Phase 7 |
