---
name: analisa-sistem
description: "Analyze system architecture by mapping components, actors, use cases, and data flows. Use when: understanding system structure, documenting business processes, identifying integrations, or tracing data transformation."
---

# Analisa Sistem (System Analysis)

## When to Use
- Understanding the overall system architecture
- Documenting how different components interact
- Identifying all actors and their capabilities
- Tracing data flow from input to storage to output
- Creating comprehensive system documentation

## Procedure

### 2.1 Identifikasi Komponen Utama (Identify Main Components)

Map all architectural components by their responsibilities:

1. **Entities / Models** — List all data models with key attributes
   - Ask: What data entities does the system manage?
   - Document: Entity name, attributes, primary key, relationships

2. **Services / Business Logic** — Identify core business logic layers
   - Ask: What calculations, transformations, or workflows happen?
   - Document: Service name, responsibilities, methods/operations

3. **Controllers / Routes** — Map all API endpoints and request handlers
   - Ask: What endpoints expose the system?
   - Document: Route path, HTTP method, handler controller, input/output

4. **Repositories / Data Access** — Identify data access patterns
   - Ask: How does the system read/write data?
   - Document: Repository name, methods (CRUD operations), database/storage target

5. **Integrations** — List external system dependencies
   - Ask: Does the system connect to external APIs or services?
   - Document: Integration name, purpose, direction (inbound/outbound), data format

### 2.2 Identifikasi Aktor & Use Case (Identify Actors & Use Cases)

For each system user (human or external system), define capabilities and permissions:

1. **List all actors**
   - Human users (by role/type: admin, operator, customer, etc.)
   - External systems (APIs, schedulers, batches, webhooks, etc.)
   - Automated processes (scheduled jobs, observers, listeners)

2. **For each actor, determine:**
   - **Identity** — Role name, system account, or integration type
   - **Use cases** — What actions can this actor perform? (e.g., "create invoice", "view dashboard", "sync inventory")
   - **Access boundaries** — What data/features are available to them? (permissions, visibility scope)

3. **Document use case details:**
   - Trigger/precondition
   - Actor actions
   - Expected outcome/postcondition

### 2.3 Analisa Alur Data (Analyze Data Flow)

Trace data movement through the system lifecycle:

1. **Input stage**
   - How does data enter the system? (User form, API, import, webhook)
   - What is the input format/structure?
   - Who/what triggers data entry?

2. **Validation stage**
   - What validation rules apply?
   - What conditions must be met?
   - What errors are possible?

3. **Process stage**
   - What transformations occur?
   - What calculations are applied?
   - What side effects happen? (logging, notifications, cache updates)

4. **Storage stage**
   - Where is data persisted? (Database table, file, cache, external service)
   - What relationships are created?
   - Is historical data retained?

5. **Output stage**
   - How is data consumed? (API response, dashboard display, export, email)
   - Who/what accesses the output?
   - What format is returned?

## Key Questions to Answer

- **What are the system's core responsibilities?** (mission/purpose)
- **Who uses it and why?** (actors and motivations)
- **What data flows through it?** (inputs → processing → outputs)
- **Where are the bottlenecks or risks?** (critical paths, data dependencies)
- **What would break first?** (most critical components, single points of failure)

## Output Format

Document findings in a structured format:

```
## Components
- Models: [list with key attributes]
- Services: [list with responsibilities]
- Controllers: [list with routes]
- Repositories: [list with CRUD operations]
- Integrations: [list with purpose and type]

## Actors & Use Cases
- Actor 1: [role/type]
  - Use cases: [list]
  - Permissions: [access boundaries]
- Actor 2: ...

## Data Flows
- Flow 1: [name]
  - Input: [source and format]
  - Processing: [transformations]
  - Storage: [persistence details]
  - Output: [delivery method and consumers]
- Flow 2: ...
```
