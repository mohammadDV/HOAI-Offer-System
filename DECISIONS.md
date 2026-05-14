## 0. AI Tooling & Workflow

This project was developed using a multi-AI workflow:

- **Cursor** was primarily used for code generation and implementation
- **ChatGPT** was used for planning, system design, and architectural breakdown
- **Claude** was used as a skill-based assistant for best practices of Laravel, Livewire, Flux and Pest improving decisions across the stack

Claude was specifically used to refine and validate approaches in:

- Laravel (architecture, service design, structuring)
- Livewire Volt (component organization and state handling)
- Flux UI (UX consistency and skeleton/loading patterns)
- Pest (testing strategy and structure)

This combination was intentionally used to ensure both fast implementation and consistent engineering quality.

---

## 1. AI-First Implementation Approach

This system was built with an AI-first workflow (estimated 80%+ of the codebase generated with AI assistance).

However, all architectural decisions, scope definition, and final refinements were made manually based on my experience as a senior developer.

My role was focused on:
- system design decisions
- simplifying scope appropriately for a trial project
- validating and correcting AI-generated output
- ensuring maintainable structure and clean separation of concerns

---

## 2. Implementation Strategy (Step-by-Step Execution Plan)

After the initial HOAI domain analysis, I decided to structure the implementation into clear iterative steps to reduce complexity and maintain control over the architecture:

- **Step 1:** Write `SPEC.md` (define scope, rules, and constraints before coding)
- **Step 2:** Models + migrations (define data structure first)
- **Step 3:** Seeder (create realistic test dataset early)
- **Step 4:** `HoaiCalculatorService` with unit tests (core business logic isolation)
- **Step 5:** Livewire UI + Volt implementation (interactive layer + state handling)
- **Step 6:** Feature / integration tests (validate user flows and system behavior)
- **Step 7:** Final documentation (`README.md`, `DECISIONS.md`, `TIME.md`)

This step-based approach helped ensure:
- controlled complexity at each stage
- early validation of business logic before UI layer
- reduced risk of architectural drift
- clear separation between domain logic, UI, and testing

---

## 3. Understanding HOAI Domain Before Implementation

Since HOAI was initially unfamiliar, I started by researching:
- general HOAI structure and terminology
- key concepts like zones, phases, and rate logic
- different possible implementation strategies (simple vs advanced)

I used iterative questioning with AI to build a clear mental model before starting implementation.

After understanding the domain, I evaluated multiple implementation approaches and selected a simplified architecture suitable for a trial system.

---

## 4. Scope Simplification (Intentional Architectural Decision)

Instead of implementing a fully dynamic or enterprise-level system, I intentionally reduced complexity:

- no dynamic pricing engine
- no admin-configurable HOAI rules
- no full regulatory HOAI table implementation

This decision was made to keep the system:
- reviewable within a short time
- aligned with trial requirements
- focused on architecture rather than completeness

Although I could have implemented a full domain-driven design approach (DDD) with complete domain modeling, including Value Objects, DTOs, repositories, and a full abstraction layer, I intentionally chose a simpler service-oriented approach.

The final implementation uses a single `HoaiCalculatorService` with a lightweight abstraction over configuration-based rules, which provides a balance between clean architecture and simplicity while avoiding unnecessary complexity for a trial scope.

---

## 5. Use of Enums (Important Decision)

Enums were explicitly used for controlled input values:

- Zone (I–V simplified representation)
- Rate (minimum, middle, maximum)

Reason:
- ensure type safety
- improve UI consistency
- prevent invalid input values
- avoid magic strings in the system

Business logic (such as percentages) was intentionally kept outside enums and handled in the service layer.

---

## 6. Calculation Architecture (HoaiCalculatorService)

All HOAI calculations were centralized in a dedicated service.

Key decisions:
- business rules are not stored in models or enums
- calculation logic is isolated in a single service class
- BCMath is used for financial precision to avoid floating-point errors

This ensures predictable and accurate financial calculations.

---

## 7. Configuration-Based Design for Rules

Zone rates and phase percentages were implemented using config-based structure instead of database tables.

Reasoning:
- rules are static in this scope
- avoids unnecessary database complexity
- keeps system lightweight and testable

Dependency injection is used to keep configuration decoupled from service logic.

---

## 8. Manual Position Simplification

For manual positions, I intentionally avoided introducing a separate service layer.

Calculation is kept simple:
- quantity × unit_price

This was a conscious trade-off based on scope simplicity.

In a larger system, this logic would typically be extracted into a dedicated service such as a `HoaiCalculatorService` or a separate pricing/domain service with proper abstraction layers. However, in this implementation I intentionally kept it inside the Action layer to maintain simplicity, reduce overhead, and keep the flow easy to review within the trial scope.

---

## 9. UI and Frontend Decisions

The UI was intentionally kept minimal:
- responsive and simple layout
- no pagination or advanced filtering
- no unnecessary UI abstractions

Focus was placed on:
- correct data relationships
- validation handling
- real-time calculation updates

---

## 10. Code Structure Simplification

The system was intentionally kept with fewer components to improve reviewability.

Example decision:
- offer.show page contains combined logic instead of splitting into many sub-components

Actions were used for create/update operations to keep Livewire components clean.

---

## 11. Testing Strategy

Testing was implemented in layers:

- unit tests for HoaiCalculatorService
- action-level tests for business operations
- minimal TDD approach for critical logic validation
- browser/E2E test for offer show flow

The goal was not full coverage, but demonstration of testing strategy across layers.

---

## 12. Production-Level Considerations (Explicitly Skipped)

To maintain simplicity, several production concerns were intentionally excluded:

- transactions
- idempotency handling
- rate limiting
- queue systems
- complex error recovery flows

Reason:
- scope is a trial/MVP system
- adding them would introduce unnecessary complexity

---

## 13. AI Behavior Observations & Engineering Feedback

During development, I observed several patterns in AI-generated code that required manual correction or architectural guidance.

---

### 13.1 Test Strategy Duplication Issue

In some cases, the AI duplicated test coverage unintentionally.

Example:
- Unit tests were written for `HoaiCalculatorService`
- The same logic was partially re-tested inside feature/action tests instead of being mocked

Observation:
- AI did not consistently distinguish between unit-level responsibility and integration-level testing boundaries

Impact:
- potential test duplication in larger systems
- unnecessary coupling between test layers

Decision:
- I manually adjusted the testing strategy to ensure:
  - unit tests validate isolated logic
  - feature/action tests focus on integration behavior
  - external services are mocked where appropriate

---

### 13.2 Lack of Architectural Constraint Awareness

AI tends to default to the simplest working solution when no strict architectural constraints are provided.

Example:
- In Livewire Volt components, logic was initially placed directly inside the component
- No separation into Actions or service boundaries was applied by default

Observation:
- without explicit architectural direction, AI prioritizes speed over structure

Decision:
- I introduced Action classes to:
  - isolate write operations
  - reduce component complexity
  - improve maintainability

This was especially important for keeping Livewire components readable even in a simplified MVP context.

---

### 13.3 Data Type Inconsistency (Critical Finding)

A type inconsistency was introduced in `sort_order` handling.

Issue:
- AI used `string` for `sort_order`

Context:
- the system already uses `string` for monetary values due to BCMath requirements

Problem:
- `sort_order` is a structural ordering field and should remain numeric
- using string caused unnecessary casting and cleanup logic (e.g. `'0'` handling)

Fix:
- converted `sort_order` to integer
- removed unnecessary string normalization step

Impact:
- improved clarity of intent
- reduced implicit type conversions
- aligned field semantics with its actual purpose

---

### 13.4 Key Takeaway: AI Requires Explicit Architectural Framing

A consistent pattern observed:

- Without explicit architectural constraints, AI chooses:
  - simplest implementation
  - minimal separation of concerns
  - flat structure over layered design

Conclusion:
- AI is highly effective for implementation speed
- but requires strict architectural guidance to maintain scalability and consistency

This was mitigated by:
- introducing explicit Actions pattern
- enforcing service-layer boundaries
- manually validating type decisions

## 14. Final Intent

This implementation prioritizes:
- simplicity over extensibility
- clarity over abstraction
- reviewability over overengineering

The goal was to demonstrate senior-level decision making under constrained scope while leveraging AI as a development accelerator rather than a replacement for engineering judgment.
