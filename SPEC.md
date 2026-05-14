# HOAI Offer System - Specification

## 1. Goal

Build a simple Offer System for managing structured offers with grouped items and HOAI-based calculated positions, while avoiding overengineering.

An offer contains:

- multiple groups
- manual positions (free cost items)
- HOAI positions (calculated cost items)
- totals at group and offer level

---

## 2. Scope

### In scope

- Create offers
- Add groups inside offers
- Add manual positions inside groups
- Add HOAI positions inside groups
- Calculate totals automatically
- Display group subtotal and offer total
- Seed one realistic offer with mixed data
- Writing tests

### Out of scope

- Authentication / users
- Multi-company support
- PDF export
- Approval workflow
- Editing HOAI rules via UI
- Full official HOAI tables
- Multi-currency support
- Overengineering
- Fancy UI tables or unnecessary display columns
- Avoided over-dynamic handling by defining phases, zones, and rates in config files.

---

## 3. Data Model

### Offer

- id
- title
- client_name (nullable)
- notes (nullable)

### OfferGroup

- id
- offer_id
- title
- sort_order

### Position (manual item)

- id
- offer_group_id
- title
- quantity // decimal(10,2) to support fractional values like 2.5 kg
- unit_price // decimal(10,2)
- total // decimal(10,2)

### HoaiPosition (calculated item)

- id
- offer_group_id
- title
- costs // decimal(10,2)
- zone (I–V) // string
- rate (minimum, middle, maximum) // string
- phases (array of selected phase numbers) // json
- construction_markup (%)
- additional_costs (%)
- vat (%)
- total

---

## 4. HOAI Calculation Rules (Simplified)

### Zone Rates

Defined in code (service layer):

- Zone I–V mapped to base percentages per rate (min/middle/max)

### Phase Rates

Each phase has a fixed percentage:

- 1–9 predefined in config/service

### Calculation Flow

1. base_fee = costs × zone_rate
2. phase_fee = base_fee × sum(selected_phases)
3. subtotal = phase_fee
4. subtotal += construction_markup 
5. subtotal += additional_costs
6. total = subtotal + VAT

---

## 5. Totals

### Position total

- manual: quantity × unit_price
- HOAI: calculated by service

### Group total

- sum of all positions + hoai_positions

### Offer total

- sum of all group totals

---

## 6. UI Structure (Livewire)

- Offer list page
- Offer detail page (main workspace)
    - groups
        - manual positions
        - HOAI positions
        - group subtotal

---

## 7. Assumptions

- HOAI logic is simplified and not legally accurate
- All financial calculations are handled in a dedicated service class
- No admin-editable pricing rules required
- System is single-user focused (no auth layer)
_ System has two different view Offer index and Offer show

---

## 8. Acceptance Criteria

- User can create an offer
- User can add groups to an offer
- User can add manual positions
- User can add HOAI positions
- System calculates totals correctly
- Seed data is available and realistic
- Clean separation between data and calculation logic
- Writing test to cover the calculate service
