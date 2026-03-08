# Visual QA Checklist

Use this checklist for visual quality audits of web pages.

## Page Information

| Field | Value |
|-------|-------|
| **URL** | `[ENTER_URL]` |
| **Date** | `[AUDIT_DATE]` |
| **Browser(s)** | Chrome / Firefox / Safari / Edge |
| **Viewport(s)** | Mobile / Tablet / Desktop |

---

## Layout Quality

### Grid & Structure

- [ ] Grid system is consistent across sections
- [ ] Content width is appropriate (not too wide)
- [ ] Sections have consistent margins
- [ ] No horizontal overflow on any viewport
- [ ] Container max-width is set appropriately

**Issues:**
```
[List layout issues]
```

### Spacing Consistency

- [ ] Section spacing is consistent (e.g., all sections have 80px gap)
- [ ] Paragraph spacing is uniform
- [ ] List item spacing is consistent
- [ ] Button padding is consistent across types
- [ ] Card padding is uniform

**Issues:**
```
[List spacing issues]
```

### Alignment

- [ ] Text is aligned consistently (left, center, right)
- [ ] Icons align with text properly
- [ ] Buttons in groups are aligned
- [ ] Form labels align with inputs
- [ ] Images are properly aligned in containers
- [ ] Navigation items are evenly spaced

**Issues:**
```
[List alignment issues]
```

---

## Typography

### Font Usage

- [ ] Font family is consistent with design system
- [ ] Font sizes follow scale (e.g., 12, 14, 16, 20, 24, 32, 48)
- [ ] Line heights are appropriate (1.4-1.6 for body)
- [ ] Font weights are used consistently
- [ ] Letter spacing is appropriate

**Issues:**
```
[List typography issues]
```

### Readability

- [ ] Body text is 16px minimum
- [ ] Line length is 45-75 characters
- [ ] Headings have proper hierarchy
- [ ] Contrast ratio is sufficient (4.5:1 minimum)
- [ ] Text is not justified (use left-align)

**Issues:**
```
[List readability issues]
```

---

## Colors

### Color Consistency

- [ ] Primary color is used consistently
- [ ] Secondary color is used consistently
- [ ] Accent colors match design system
- [ ] Background colors are consistent
- [ ] Text colors follow hierarchy (primary, secondary, muted)

**Issues:**
```
[List color inconsistencies]
```

### Contrast & Accessibility

- [ ] Body text meets 4.5:1 contrast
- [ ] Large text meets 3:1 contrast
- [ ] Interactive elements have 3:1 contrast
- [ ] Placeholder text is visible
- [ ] Disabled states are distinguishable

**Issues:**
```
[List contrast failures]
```

---

## Interactive Elements

### Buttons

- [ ] Primary buttons are visually prominent
- [ ] Secondary buttons are clearly different
- [ ] Hover states are visible
- [ ] Focus states are visible
- [ ] Active/pressed states are visible
- [ ] Disabled states are clear
- [ ] Loading states are indicated
- [ ] Button sizes are consistent (sm, md, lg)
- [ ] Touch targets are 44x44px minimum

**Issues:**
```
[List button issues]
```

### Links

- [ ] Links are distinguishable from regular text
- [ ] Hover states are visible
- [ ] Focus states are visible
- [ ] Visited links are styled (optional)
- [ ] External links are indicated
- [ ] Links that open new tabs are indicated

**Issues:**
```
[List link issues]
```

### Forms

- [ ] Input fields have consistent styling
- [ ] Labels are visible and associated
- [ ] Placeholder text is helpful
- [ ] Focus states are clear
- [ ] Error states are visible
- [ ] Success states are visible
- [ ] Required fields are indicated
- [ ] Validation messages are clear

**Issues:**
```
[List form issues]
```

---

## Images & Media

### Images

- [ ] Images are not pixelated/stretched
- [ ] Images have appropriate aspect ratios
- [ ] Images are properly compressed
- [ ] Lazy loading is implemented
- [ ] Alt text is present
- [ ] Placeholder/loading states exist
- [ ] Broken images are handled

**Issues:**
```
[List image issues]
```

### Icons

- [ ] Icons are consistent in style
- [ ] Icon sizes are appropriate
- [ ] Icons are properly aligned
- [ ] Icon colors match design system
- [ ] Icons have appropriate spacing
- [ ] Icon-only buttons have aria-labels

**Issues:**
```
[List icon issues]
```

---

## Components

### Navigation

- [ ] Logo is properly positioned
- [ ] Navigation items are evenly spaced
- [ ] Active state is clear
- [ ] Mobile menu works correctly
- [ ] Dropdown menus align properly
- [ ] Mega menus display correctly

**Issues:**
```
[List navigation issues]
```

### Cards

- [ ] Card shadows are consistent
- [ ] Card borders are consistent
- [ ] Card padding is uniform
- [ ] Card hover effects are smooth
- [ ] Card content is aligned
- [ ] Card images fit properly

**Issues:**
```
[List card issues]
```

### Modals

- [ ] Modal backdrop is visible
- [ ] Modal is centered
- [ ] Modal close button is visible
- [ ] Modal doesn't overflow viewport
- [ ] Modal traps focus correctly
- [ ] Escape key closes modal

**Issues:**
```
[List modal issues]
```

### Tables

- [ ] Table headers are styled
- [ ] Row hover states are visible
- [ ] Columns are aligned properly
- [ ] Tables are responsive (or have scroll)
- [ ] Empty states are handled
- [ ] Pagination is clear

**Issues:**
```
[List table issues]
```

---

## Responsive Design

### Mobile (< 768px)

- [ ] Content is fully visible
- [ ] No horizontal scroll
- [ ] Touch targets are 44x44px
- [ ] Text is readable without zoom
- [ ] Navigation collapses to hamburger
- [ ] Images scale appropriately
- [ ] Forms are usable

**Issues:**
```
[List mobile issues]
```

### Tablet (768px - 1024px)

- [ ] Layout adjusts appropriately
- [ ] Navigation displays correctly
- [ ] Grid columns adjust
- [ ] Images scale appropriately
- [ ] Touch targets are adequate

**Issues:**
```
[List tablet issues]
```

### Desktop (> 1024px)

- [ ] Content width is controlled
- [ ] White space is balanced
- [ ] Hover effects work
- [ ] All content is accessible
- [ ] Layout doesn't break at large sizes

**Issues:**
```
[List desktop issues]
```

---

## Animation & Transitions

### Motion Quality

- [ ] Animations are smooth (60fps)
- [ ] Transitions are not too slow (< 300ms)
- [ ] Transitions are not too fast (> 100ms)
- [ ] Hover effects are immediate
- [ ] Loading states are animated
- [ ] Animations don't cause motion sickness

**Issues:**
```
[List animation issues]
```

### Motion Accessibility

- [ ] `prefers-reduced-motion` is respected
- [ ] Essential animations are subtle
- [ ] No auto-playing animations (or can pause)

**Issues:**
```
[List motion accessibility issues]
```

---

## Performance

### Loading

- [ ] Page loads in < 3 seconds
- [ ] Critical content loads first
- [ ] Images use lazy loading
- [ ] Fonts don't cause FOIT/FOUT
- [ ] Loading states are shown

**Issues:**
```
[List loading issues]
```

### CLS (Cumulative Layout Shift)

- [ ] No unexpected layout shifts
- [ ] Images have defined dimensions
- [ ] Fonts are preloaded
- [ ] Dynamic content has reserved space

**Issues:**
```
[List CLS issues]
```

---

## Browser-Specific

### Chrome

- [ ] All features work
- [ ] No visual bugs
- [ ] Performance is acceptable

### Firefox

- [ ] All features work
- [ ] No visual bugs
- [ ] Scrollbar styling (if customized)

### Safari

- [ ] All features work
- [ ] No visual bugs
- [ ] Date inputs render correctly
- [ ] Scroll behavior is smooth

### Edge

- [ ] All features work
- [ ] No visual bugs

**Issues:**
```
[List browser-specific issues]
```

---

## Summary

### Overall Quality Score

| Category | Score (1-5) |
|----------|-------------|
| Layout | `[SCORE]` |
| Typography | `[SCORE]` |
| Colors | `[SCORE]` |
| Interactions | `[SCORE]` |
| Responsive | `[SCORE]` |
| Performance | `[SCORE]` |
| **Average** | `[AVG_SCORE]` |

### Priority Fixes

1. **[Priority 1]**: [Description]
2. **[Priority 2]**: [Description]
3. **[Priority 3]**: [Description]

### Recommendations

```
[General recommendations for improvement]
```

---

## Screenshots

- Desktop: `[PATH_TO_SCREENSHOT]`
- Tablet: `[PATH_TO_SCREENSHOT]`
- Mobile: `[PATH_TO_SCREENSHOT]`
- Issue Highlights: `[PATH_TO_ANNOTATED_SCREENSHOTS]`
