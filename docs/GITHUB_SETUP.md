# GitHub-Setup: Branch-Protection für `main`

Da auf der Build-Maschine `gh` (GitHub CLI) nicht installiert und `gh auth login`
interaktiv ist, kann der Schutz **nicht automatisiert** gesetzt werden. Hier die
Web-UI-Schritte (2 Minuten). Repo: `karadumantrading/stationszimmer-humhub-jobs`.

## Variante A – Ruleset (empfohlen, moderne UI)

1. Repo → **Settings → Rules → Rulesets → New ruleset → New branch ruleset**.
2. **Name:** `protect-main`. **Enforcement status:** *Active*.
3. **Target branches → Add target → Include default branch** (= `main`).
4. Regeln aktivieren:
   - ☑ **Restrict deletions** (Branch kann nicht gelöscht werden)
   - ☑ **Block force pushes** (kein `git push --force`)
   - *(optional, wenn mit jemandem zusammengearbeitet wird)* ☑ **Require a pull
     request before merging** (für Solo-Arbeit eher weglassen, sonst blockiert es
     direkte Pushes)
   - *(optional)* ☑ **Require linear history**
5. **Create**.

## Variante B – Classic Branch protection

1. Repo → **Settings → Branches → Add branch protection rule**.
2. **Branch name pattern:** `main`.
3. ☑ *Do not allow bypassing the above settings* (optional), ☑ *Require linear
   history* (optional). **Force pushes/Deletions** bleiben standardmässig
   verboten, sobald eine Regel existiert.
4. **Create / Save**.

## Hinweis Solo-Betrieb

Wenn du allein direkt auf `main` pushst (wie bisher), aktiviere **nur**
*Restrict deletions* + *Block force pushes* und **keinen** PR-Zwang – sonst musst
du jede Änderung über einen Pull Request mergen.

## Später automatisierbar

Sobald `gh` installiert + `gh auth login` erfolgt ist, ginge das auch per CLI:
```
gh api -X PUT repos/karadumantrading/stationszimmer-humhub-jobs/branches/main/protection \
  -f "required_status_checks=null" -F "enforce_admins=true" \
  -f "required_pull_request_reviews=null" -f "restrictions=null" \
  -F "allow_force_pushes=false" -F "allow_deletions=false"
```
