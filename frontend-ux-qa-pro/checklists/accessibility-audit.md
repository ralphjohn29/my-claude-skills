# Accessibility Audit Checklist

Use this checklist when auditing pages for WCAG 2.1 AA compliance.

## Page Information

| Field | Value |
|-------|-------|
| **URL** | `[ENTER_URL]` |
| **Date** | `[AUDIT_DATE]` |
| **Auditor** | `[AUDITOR_NAME]` |
| **Viewport Tested** | Desktop / Tablet / Mobile |

---

## Level A Requirements (Critical)

### 1. Non-text Content (1.1.1)

- [ ] All `<img>` elements have `alt` attribute
- [ ] Decorative images have empty `alt=""`
- [ ] Informative images have descriptive alt text
- [ ] Complex images have extended description
- [ ] SVG icons have `aria-hidden="true"` or labels
- [ ] Background images conveying info have text alternative

**Issues Found:**
```
[List any missing or poor alt text]
```

### 2. Info and Relationships (1.3.1)

- [ ] Headings use `<h1>` through `<h6>` tags
- [ ] Only one `<h1>` per page
- [ ] Heading hierarchy is logical (no skipping)
- [ ] Lists use proper `<ul>`, `<ol>`, `<li>` tags
- [ ] Tables have proper `<th>` and scope attributes
- [ ] Form fields have associated `<label>` elements
- [ ] Fieldsets group related form controls

**Issues Found:**
```
[List any structural issues]
```

### 3. Meaningful Sequence (1.3.2)

- [ ] Reading order makes sense when CSS is disabled
- [ ] Tab order follows visual order
- [ ] Content is not positioned off-screen for hiding

**Issues Found:**
```
[List any sequence issues]
```

### 4. Use of Color (1.4.1)

- [ ] Color is not the only means of conveying information
- [ ] Links are distinguishable from surrounding text
- [ ] Error states don't rely solely on red color
- [ ] Required fields have more than color indication

**Issues Found:**
```
[List any color-only indicators]
```

### 5. Keyboard Accessible (2.1.1)

- [ ] All interactive elements are focusable
- [ ] All functionality works with keyboard
- [ ] No keyboard traps
- [ ] Custom controls are keyboard accessible
- [ ] Focus can be moved away from all components

**Issues Found:**
```
[List any keyboard accessibility issues]
```

### 6. No Keyboard Trap (2.1.2)

- [ ] Focus can leave modals
- [ ] Focus can leave dropdown menus
- [ ] No infinite focus loops

**Issues Found:**
```
[List any keyboard traps]
```

### 7. Bypass Blocks (2.4.1)

- [ ] Skip navigation link is present
- [ ] Skip link works correctly
- [ ] Landmarks are properly used (main, nav, aside)

**Issues Found:**
```
[List any bypass issues]
```

### 8. Page Titled (2.4.2)

- [ ] Page has unique `<title>` element
- [ ] Title describes page content/purpose
- [ ] Title is concise but informative

**Issues Found:**
```
[List any title issues]
```

### 9. Focus Order (2.4.3)

- [ ] Tab order is logical
- [ ] Focus moves to expected elements
- [ ] Modals receive focus when opened
- [ ] Focus returns to trigger when modal closes

**Issues Found:**
```
[List any focus order issues]
```

### 10. Link Purpose (2.4.4)

- [ ] Link text is descriptive
- [ ] "Read more" links have context
- [ ] Links open in new tabs are indicated
- [ ] Image links have alt text describing destination

**Issues Found:**
```
[List any unclear link purposes]
```

### 11. Language of Page (3.1.1)

- [ ] `<html>` element has `lang` attribute
- [ ] Language code is correct

**Issues Found:**
```
[List any language issues]
```

### 12. Labels or Instructions (3.3.2)

- [ ] Form fields have visible labels
- [ ] Required fields are indicated
- [ ] Input format requirements are shown
- [ ] Instructions are provided where needed

**Issues Found:**
```
[List any missing labels/instructions]
```

---

## Level AA Requirements (Standard)

### 13. Contrast Minimum (1.4.3)

- [ ] Normal text has 4.5:1 contrast ratio
- [ ] Large text (18pt+) has 3:1 contrast ratio
- [ ] Placeholder text meets contrast requirements
- [ ] Disabled text is exempt but still readable

**Issues Found:**
```
[List any contrast failures with specific values]
```

### 14. Resize Text (1.4.4)

- [ ] Text scales to 200% without loss of content
- [ ] No horizontal scrolling at 200% zoom
- [ ] Text containers expand appropriately

**Issues Found:**
```
[List any resize issues]
```

### 15. Focus Visible (2.4.7)

- [ ] All focusable elements have visible focus indicator
- [ ] Focus indicator has sufficient contrast
- [ ] Custom focus styles are obvious

**Issues Found:**
```
[List any missing focus indicators]
```

### 16. Consistent Navigation (3.2.3)

- [ ] Navigation is in same location across pages
- [ ] Navigation order is consistent
- [ ] Navigation styling is consistent

**Issues Found:**
```
[List any navigation inconsistencies]
```

### 17. Consistent Identification (3.2.4)

- [ ] Same components look and behave the same
- [ ] Icons have consistent meaning
- [ ] Buttons have consistent styling

**Issues Found:**
```
[List any identification inconsistencies]
```

---

## Summary

### Issue Count by Severity

| Severity | Count |
|----------|-------|
| Critical (A) | `[COUNT]` |
| Important (AA) | `[COUNT]` |
| **Total** | `[TOTAL]` |

### Pass Rate

- Level A: `[PASSED]` / `[TOTAL]` = `[PERCENTAGE]%`
- Level AA: `[PASSED]` / `[TOTAL]` = `[PERCENTAGE]%`

### Overall Assessment

```
[Provide overall accessibility assessment and priority recommendations]
```

---

## Tools Used

- [ ] axe DevTools
- [ ] WAVE
- [ ] Lighthouse
- [ ] Keyboard navigation test
- [ ] Screen reader test (VoiceOver/NVDA)
- [ ] Color contrast checker

## Next Steps

1. [Priority action item]
2. [Priority action item]
3. [Priority action item]
