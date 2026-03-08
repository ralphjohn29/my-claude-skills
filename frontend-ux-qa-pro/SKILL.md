---
name: frontend-ux-qa-pro
description: Professional Frontend UI/UX Quality Assurance skill using Playwright MCP. Use PROACTIVELY when auditing websites, testing UI/UX quality, checking accessibility, validating layouts, detecting AI slop, and generating comprehensive QA reports. Covers visual regression, spacing/alignment checks, accessibility compliance (WCAG), performance metrics, and cross-browser testing. Triggers for UI audits, UX reviews, frontend bug hunting, and quality reports. Outputs markdown reports for other skills to fix issues.
---

# Frontend UI/UX QA Pro

A comprehensive frontend quality assurance skill that acts as your professional QA consultant. Uses Playwright MCP to audit websites, identify UI/UX issues, and generate actionable reports.

## Core Philosophy

**QA as a Consultant** - This skill doesn't just find bugs; it provides:
- Clear issue identification with severity levels
- Root cause analysis (Frontend vs Backend)
- Actionable recommendations
- Handoff-ready reports for developers

---

## Quick Start

### Running a UI/UX Audit

```bash
# The skill uses Playwright MCP tools to:
# 1. Navigate to the target page
# 2. Take snapshots and screenshots
# 3. Analyze layout, accessibility, and visual issues
# 4. Generate a comprehensive markdown report
```

### Using Playwright MCP Tools

The skill leverages these Playwright MCP tools:
- `browser_navigate` - Navigate to target URL
- `browser_snapshot` - Capture page structure
- `browser_take_screenshot` - Visual capture
- `browser_evaluate` - Run custom JavaScript checks
- `browser_console_messages` - Check for JS errors

---

## Testing Categories

### 1. Visual Layout Testing

**What We Check:**
- Spacing consistency (padding, margins, gaps)
- Alignment of elements (text, images, icons, buttons)
- Grid/flex layout integrity
- Responsive breakpoints
- Visual hierarchy

**Common Issues Detected:**
| Issue | Type | Fix Location |
|-------|------|--------------|
| Inconsistent padding | CSS | Frontend |
| Misaligned text | CSS | Frontend |
| Broken grid | CSS | Frontend |
| Overflow issues | CSS | Frontend |
| Z-index conflicts | CSS | Frontend |

### 2. Accessibility Testing (WCAG 2.1 AA)

**What We Check:**
- Color contrast ratios (4.5:1 for text)
- Focus indicators visibility
- Alt text for images
- ARIA labels and roles
- Keyboard navigation
- Screen reader compatibility
- Form label associations

**Common Issues Detected:**
| Issue | WCAG Level | Severity |
|-------|------------|----------|
| Low contrast | AA | High |
| Missing alt text | A | Critical |
| No focus indicator | AA | High |
| Missing form labels | A | Critical |
| Invalid ARIA | A | Medium |

### 3. Interactive Elements Testing

**What We Check:**
- Button states (hover, active, disabled, focus)
- Link functionality and indicators
- Form validation feedback
- Loading states
- Error handling UI
- Touch target sizes (min 44x44px)

**Common Issues Detected:**
| Issue | Type | Fix Location |
|-------|------|--------------|
| No hover state | CSS | Frontend |
| Small touch targets | CSS | Frontend |
| Missing disabled state | CSS/JS | Frontend |
| Broken links | HTML/API | Varies |
| Form validation | JS | Frontend |

### 4. Performance & Loading

**What We Check:**
- Page load time
- Console errors
- Network request failures
- Image optimization
- Layout shift (CLS)
- First contentful paint

**Common Issues Detected:**
| Issue | Type | Fix Location |
|-------|------|--------------|
| Large images | Asset | Frontend/Backend |
| Console errors | JS | Frontend |
| 404 resources | Network | Frontend |
| API failures | Network | Backend |
| Slow TTFB | Server | Backend |

### 5. AI Content Quality Detection ("AI Slop")

**What We Check:**
- Generic/placeholder text patterns
- Repetitive content structure
- Unnatural phrasing
- Lorem ipsum leftovers
- Inconsistent tone
- Missing personalization

**Common AI Slop Indicators:**
| Pattern | Detection | Severity |
|---------|-----------|----------|
| "In today's digital age" | Text scan | Low |
| Repeated phrases | Text scan | Medium |
| Generic CTAs | Text scan | Low |
| Placeholder text | Regex | High |
| Unfinished content | Manual | Critical |

---

## Issue Classification

### Severity Levels

```
CRITICAL  - Blocks user flow, accessibility violation, security issue
HIGH      - Major UX problem, broken functionality
MEDIUM    - Noticeable issue, affects experience
LOW       - Minor polish, nice to have
INFO      - Suggestion, best practice recommendation
```

### Issue Type Classification

```javascript
// Frontend Issues - CSS/HTML/JS
const frontendIndicators = [
  'CSS syntax error',
  'JavaScript console error',
  'Layout breakage',
  'Missing hover states',
  'Responsive issues',
  'Animation glitches',
];

// Backend Issues - API/Server
const backendIndicators = [
  '500 server error',
  'API timeout',
  'Database connection error',
  'Authentication failure',
  'Slow TTFB',
  'Invalid response format',
];

// Mixed Issues - Could be either
const mixedIndicators = [
  '404 not found', // Could be frontend route or backend resource
  'Form submission', // Could be validation or API
  'Image not loading', // Could be path or CDN
];
```

---

## Playwright MCP Testing Commands

### Basic Page Audit

```javascript
// Navigate and snapshot
await browser_navigate({ url: 'https://example.com' });
await browser_snapshot();

// Check for console errors
const messages = await browser_console_messages();
const errors = messages.filter(m => m.type === 'error');

// Take screenshot for visual review
await browser_take_screenshot({
  path: 'audit-screenshot.png',
  fullPage: true
});
```

### Accessibility Check

```javascript
// Run axe-core accessibility scan
await browser_evaluate({
  script: `
    // Check color contrast
    const elements = document.querySelectorAll('*');
    const issues = [];

    elements.forEach(el => {
      const style = getComputedStyle(el);
      // Check contrast, font-size, etc.
    });

    return issues;
  `
});
```

### Layout Analysis

```javascript
// Analyze spacing and alignment
await browser_evaluate({
  script: `
    const results = {
      alignment: [],
      spacing: [],
      overflow: []
    };

    // Check element alignments
    document.querySelectorAll('section, div, header, footer').forEach(el => {
      const rect = el.getBoundingClientRect();
      const style = getComputedStyle(el);

      // Check for alignment issues
      if (rect.left < 0 || rect.right > window.innerWidth) {
        results.overflow.push({
          selector: el.id || el.className,
          issue: 'Horizontal overflow'
        });
      }
    });

    return results;
  `
});
```

---

## Report Generation

### Report Structure

The skill generates markdown reports with this structure:

```markdown
# UI/UX QA Audit Report

**URL:** https://example.com
**Date:** 2025-01-15
**Auditor:** Frontend UX QA Pro

## Executive Summary
- Total Issues: 12
- Critical: 2 | High: 4 | Medium: 4 | Low: 2
- Frontend: 8 | Backend: 2 | Mixed: 2

## Critical Issues
### 1. Missing Form Labels (A11y)
**Location:** Contact form, email input
**Issue:** Input has no associated label
**WCAG:** 1.3.1 Info and Relationships (A)
**Fix:** Add `<label for="email">Email</label>`

## High Issues
...

## Medium Issues
...

## Low Issues
...

## Recommendations
...

## AI Content Analysis
- Lorem ipsum detected: 2 instances
- Generic phrases: 3 instances
- Recommendation: Replace placeholder content

## Performance Metrics
- Page Load: 2.3s
- Console Errors: 3
- Network Failures: 1

## Next Steps
1. Fix critical accessibility issues
2. Address console errors
3. Optimize images
```

---

## Reference Files

For detailed testing guides, see:
- `references/accessibility-testing.md` - WCAG compliance testing
- `references/visual-regression.md` - Screenshot comparison testing
- `references/layout-analysis.md` - Spacing and alignment patterns
- `references/performance-metrics.md` - Core Web Vitals testing
- `references/ai-content-detection.md` - AI slop identification

---

## Checklists

Pre-built checklists for common scenarios:
- `checklists/accessibility-audit.md` - A11y compliance checklist
- `checklists/visual-qa.md` - Visual quality checklist
- `checklists/responsive-testing.md` - Cross-device testing
- `checklists/form-testing.md` - Form UX checklist

---

## Templates

Ready-to-use report templates:
- `templates/audit-report.md` - Full audit report template
- `templates/issue-ticket.md` - Bug ticket template
- `templates/accessibility-report.md` - A11y-focused report

---

## Best Practices

### When Testing

1. **Test as a User** - Navigate like a real user would
2. **Check Multiple Viewports** - Mobile, tablet, desktop
3. **Wait for Stability** - Let animations and loads complete
4. **Document with Screenshots** - Visual evidence for every issue
5. **Verify Network State** - Check console and network tabs

### When Reporting

1. **Be Specific** - Exact selectors, line numbers, URLs
2. **Show Impact** - Why this matters for users
3. **Provide Solutions** - Don't just identify, suggest fixes
4. **Prioritize** - Use severity levels consistently
5. **Enable Handoff** - Reports should be developer-ready

---

## Integration with Other Skills

This skill generates reports that can be consumed by:
- **frontend-design** - To fix visual issues
- **code-reviewer** - For code-level fixes
- **laravel-api-expert** - For backend API issues
- **senior-frontend** - For React/Vue component fixes
- **laravel-backend-expert** - For Laravel-specific backend issues

---

## Quick Reference

### Playwright MCP Tool Checklist

- [ ] `browser_navigate` - Go to target URL
- [ ] `browser_snapshot` - Get page structure
- [ ] `browser_take_screenshot` - Visual evidence
- [ ] `browser_console_messages` - Check JS errors
- [ ] `browser_network_requests` - Check API calls
- [ ] `browser_evaluate` - Run custom checks
- [ ] `browser_resize` - Test responsive views

### Common Selectors for Testing

```css
/* Layout containers */
header, footer, main, section, article, aside

/* Interactive elements */
button, a, input, select, textarea, [role="button"]

/* Media */
img, video, svg, canvas, iframe

/* Accessibility landmarks */
[role="navigation"], [role="main"], [role="banner"],
[aria-label], [aria-labelledby], [aria-describedby]
```
