# SARAKI — Maintenance Checklist

Routine upkeep after go-live. Estimate: ~15 min/day, ~1 h/week, ~half day/month.

## Daily (admin dashboard, ~5 min)

- [ ] Unread contact messages answered (`/admin/messages`)
- [ ] Today's appointments confirmed/prepared (`/admin/appointments`)
- [ ] Orders advanced through statuses; COD/transfer payments reconciled
- [ ] Any failed Paystack payment flagged to the customer (retry link is on
      the order page and in the failure email)
- [ ] Journal post scheduled for today published

## Weekly (~1 h)

- [ ] Stock sanity: variants at zero stock that should be restocked
      (dashboard stats → out-of-stock count)
- [ ] `storage/logs/laravel-*.log` skimmed for recurring errors
      (`grep -i "error\|exception" storage/logs/laravel-$(date +%F).log | tail`)
- [ ] Backup archives present in `storage/app/backups/` and the off-server
      copy current (`db-*.sql.gz` + `uploads-*.zip`, same timestamp pair)
- [ ] Uptime: `/up` returns 200; spot-check homepage in both locales

## Monthly (~half day)

- [ ] `composer audit` + `npm audit` review; apply patch-level updates
      (`composer update --lock` for deps, full review before majors)
- [ ] Rotate nothing routinely, but verify: `APP_KEY` backed up offline,
      DB password in the client's vault, Paystack keys recorded
- [ ] **Restore drill**: pick last week's `db-*.sql.gz`, restore into a
      scratch database (`mysql scratch < dump.sql`), confirm tables import —
      a backup never tested is a hope, not a backup
- [ ] Review `docs/uat/bug-log.md` deferred items; schedule fixes
- [ ] Check Paystack dashboard for disputes/chargebacks; review webhook
      delivery log (Paystack → Settings → Webhooks)
- [ ] Confirm SSL certificate renewal is automatic on the host

## Quarterly / as needed

- [ ] Content refresh: journal posts, gallery additions, seasonal copy FR+EN
- [ ] Measurement-sheet completeness for upcoming fittings
- [ ] Shipping zones and fees still match actual delivery costs
- [ ] Re-read `docs/decisions-and-environments.md` — close any open decisions
      that have since been answered

## Escalation

| Symptom | First check | Escalate to |
|---|---|---|
| Site down / 500 | `/up`, `storage/logs/laravel-*.log` tail | Developer |
| Payments failing | Paystack dashboard status, webhook deliveries, `payments` table latest rows | Developer + Paystack support |
| Emails not arriving | `MAIL_*` config, spam folder, SPF/DKIM | Hosting provider |
| Backups missing | cron entry, `schedule:list`, disk space | Developer |
| Suspected breach | disable affected accounts, rotate `APP_KEY`+DB password, check logs | Developer immediately |
