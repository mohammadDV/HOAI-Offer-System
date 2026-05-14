Total project duration: ~5 hours (wall-clock)

This project was executed in a structured step-by-step flow:

---

## Step 1 — SPEC.md

- Wall-clock: ~40–50 min
- Active time: ~45 min

- Defined scope and constraints
- Understood HOAI domain at a high level
- Designed simplified architecture approach

---

## Step 2 — Models + Migrations

- Wall-clock: ~25–30 min
- Active time: ~25 min

- Designed database structure
- Implemented offers, groups, positions, hoai_positions
- Kept schema minimal and review-friendly

---

## Step 3 — Seeder and Factory

- Wall-clock: ~20–25 min
- Active time: ~20 min

- Created realistic sample offer
- Added mixed data (groups, positions, HOAI positions)
- Ensured dataset reflects real-world structure

---

## Step 4 — HoaiCalculatorService + Tests

- Wall-clock: ~60–70 min
- Active time: ~60 min

- Implemented HOAI calculation logic
- Used BCMath for financial precision
- Integrated zone/rate/phase rules
- Added unit tests for core calculations

---

## Step 5 — Livewire UI (Volt + Flux)

- Wall-clock: ~90–100 min
- Active time: ~90 min

- Built Offers UI and detail page
- Implemented groups and nested structure
- Added HOAI form with enum-based selects
- Integrated Flux skeletons and loading states
- Connected UI to service layer

---

## Step 6 — Testing (Feature + E2E)

- Wall-clock: ~35–40 min
- Active time: ~35 min

- Added feature tests for workflows
- Verified offer creation and calculation flow
- Ensured separation between unit and integration tests

---

## Step 7 — Documentation (README + DECISIONS + TIME)

- Wall-clock: ~20–25 min
- Active time: ~20 min

- Finalized architectural documentation
- Documented AI usage and decisions
- Summarized time breakdown and workflow

---

## Summary

- Total wall-clock time: ~5 hours
- Active coding time: ~3.5–4 hours

Note:
A significant part of implementation was accelerated using AI tools (Cursor, ChatGPT, Claude), while all architectural decisions, simplifications, and validations were manually controlled.
