---
name: prompt-polish
description: Transform rough ideas into clear, effective AI prompts. Use this skill whenever the user shares rough thoughts, half-formed ideas, or unstructured text that they want to use as a prompt for an AI agent. Triggers for phrases like "improve my prompt", "polish this prompt", "make this prompt better", "help me write a prompt", or when the user shares rough text meant for another AI. Output is always in paragraph format only - no bullets, no lists, no formatting.
---

# Prompt Polish

Transform your rough ideas into clear, effective prompts that AI agents can fully understand and execute.

## When to use this skill

Use this skill when the user:
- Shares rough thoughts or half-formed ideas
- Wants to improve a prompt for another AI agent
- Says things like "polish this prompt" or "make this clearer for AI"
- Provides unstructured text that needs to become a proper prompt

## Core Principles

### DO:
- Clarify the goal - what does the user actually want the AI to do?
- Add necessary context that might be missing
- Remove ambiguity and vagueness
- Structure the prompt logically (but in paragraph form)
- Include constraints, requirements, or preferences the user mentioned
- Make it specific and actionable
- Preserve the user's intent completely

### DON'T:
- Use bullets, lists, or numbered items
- Add markdown formatting like headers or bold
- Change what the user is asking for
- Over-complicate or add unnecessary details
- Make it sound robotic or overly formal
- Add things the user didn't ask for

## Output Format

**IMPORTANT:** Always return the improved prompt in paragraph format only. One paragraph or multiple paragraphs depending on complexity. NO bullets, NO lists, NO numbered items, NO markdown headers, NO special formatting. Just plain text paragraphs.

## How to Polish a Prompt

1. **Understand the goal** - What is the user trying to accomplish?
2. **Identify missing context** - What would an AI need to know to do this well?
3. **Remove ambiguity** - Make everything specific and clear
4. **Structure naturally** - Organize into logical paragraphs
5. **Keep it human** - The prompt should sound natural, not like a template

## Examples

**Rough input:** "i want to build a landing page for my saas its about project management for small teams"

**Polished output:** Build a landing page for a SaaS product focused on project management designed specifically for small teams. The page should clearly communicate the value proposition of helping small teams organize their work, collaborate effectively, and stay on top of tasks without the complexity of enterprise tools. Include a hero section with a compelling headline and call-to-action, a features section highlighting key capabilities like task management, team collaboration, and progress tracking, a pricing section with plans suitable for small business budgets, and a footer with contact information and social links. The design should feel modern, trustworthy, and approachable to appeal to small business owners and startup teams.

**Rough input:** "fix my code its not working when i click button nothing happens"

**Polished output:** Debug and fix an issue where a button click is not triggering any action. The button appears to render correctly but does not respond when clicked. Please investigate the event handler, check if the click listener is properly attached, verify there are no JavaScript errors preventing execution, and ensure any async operations are being called correctly. After identifying the root cause, provide a fixed version of the code along with a brief explanation of what was wrong and how the fix resolves it.

**Rough input:** "write me an email to my boss asking for raise ive been here 2 years and did good work"

**Polished output:** Write a professional email to request a salary review meeting with my manager. I have been with the company for two years and during this time I have consistently delivered quality work, taken on additional responsibilities, and contributed to team successes. The email should be polite but confident, express appreciation for the opportunities I've had, highlight my contributions and growth, and propose a time to discuss compensation in person. Keep the tone professional and respectful while making a clear case for why a raise is warranted.

## Output

Return ONLY the polished prompt in paragraph form. No explanations, no "Here's your improved prompt" - just the improved prompt itself.
