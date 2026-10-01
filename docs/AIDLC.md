# 🔄 AI Development Life Cycle (AIDLC)

The standard workflow for every AI Agent implementing new features or fixing bugs in production.

## Phase 1: Analyze & Discover
- **Do not guess.** Use search tools (`Grep`, `SemanticSearch`) to map out the current file architecture.
- Read existing code files before modifying to learn the codebase coding patterns and style.
- Check `docs/CHANGELOG_AI.md` to understand recent implementations.

## Phase 2: Design & Spec
- If the task is a new feature or major refactoring, draft a spec proposal from `docs/FEATURE_SPEC_TEMPLATE.md` to discuss with the User.
- Cross-reference your design against `docs/SECURITY_RULES.md` (security) and `docs/PRODUCT_RULES.md` (business domain rules).

## Phase 3: Test-Driven Development (TDD)
- Write Unit or Integration tests *before* modifying code whenever possible.
- Refer to `docs/TESTING_GUIDE.md` for test execution instructions.

## Phase 4: Implementation
- Implement code changes carefully, handling edge cases gracefully (null safety, loading states, empty lists).
- Preserve all unrelated comments and docstrings.

## Phase 5: Verify & Log
- Compile/build the entire project to ensure no lint or syntax errors are introduced.
- Append a detailed summary of your changes to `docs/CHANGELOG_AI.md` before concluding the session.
