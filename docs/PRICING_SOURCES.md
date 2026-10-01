# Pricing catalog provenance

The bundled `resources/data/prices.json` is catalog version 1 with an effective snapshot date of 2026-02-18. It uses USD per one million input/output tokens.

Provider pricing references:

- OpenAI: <https://openai.com/api/pricing/>
- Google Gemini: <https://ai.google.dev/gemini-api/docs/pricing>
- Anthropic: <https://docs.anthropic.com/en/docs/about-claude/pricing>

Provider prices change independently and model aliases may have different rates. Treat the bundled file as a dated estimate, not an invoice. Owners can add a manual database override; remote synchronization deliberately preserves it. Models missing from the catalog are recorded with price unavailable.
