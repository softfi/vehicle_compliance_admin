# Tonnage Formula - Quick Start Guide (त्वरित शुरुआत गाइड)

## 🚀 3 Simple Steps (3 सरल कदम)

### Step 1: Set बनाएं
1. `/admin/set_master` पर जाएं
2. Set Name enter करें (जैसे: "Set A")
3. Submit करें

### Step 2: Tonnage Formula Add करें
1. `/admin/tonnage` पर जाएं
2. Set select करें
3. Form fill करें:
   - **Min:** 0
   - **Max:** 34.99
   - **Penalty Type:** percentage
   - **Penalty Value:** 5
   - **Bonus Type:** fixed
   - **Bonus Value:** 0
4. Submit करें

### Step 3: View Formulas
- Same page पर selected Set के सभी formulas दिखेंगे
- Edit/Delete कर सकते हैं

## 📋 Form Fields Explanation

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| **Min** | ✅ Yes | Minimum tonnage range | 0, 35 |
| **Max** | ✅ Yes | Maximum tonnage range | 34.99, 35.99 |
| **Penalty Type** | ❌ No | `percentage` या `fixed` | percentage |
| **Penalty Value** | ❌ No | Penalty amount | 5 |
| **Bonus Type** | ❌ No | `percentage` या `fixed` | percentage |
| **Bonus Value** | ❌ No | Bonus amount | 10 |
| **Weight** | ❌ No | Display text | "<35MT" |
| **Price** | ❌ No | Base price | 1000 |

## 💡 Example Scenarios

### Scenario 1: Low Tonnage Penalty
```
Min: 0
Max: 34.99
Penalty Type: percentage
Penalty Value: 5
Bonus Type: (empty)
Bonus Value: 0
```
**Result:** 0-34.99 range में 5% penalty

### Scenario 2: Standard Range Bonus
```
Min: 35
Max: 35.99
Penalty Type: (empty)
Penalty Value: 0
Bonus Type: percentage
Bonus Value: 10
```
**Result:** 35-35.99 range में 10% bonus

### Scenario 3: High Tonnage Bonus
```
Min: 36
Max: 1000
Penalty Type: (empty)
Penalty Value: 0
Bonus Type: percentage
Bonus Value: 20
```
**Result:** 36-1000 range में 20% bonus

## ⚡ Quick Commands

```bash
# Migration run करें
php spark migrate

# Check migration status
php spark migrate:status
```

## 🔗 Important URLs

- Set Master: `/admin/set_master`
- Tonnage: `/admin/tonnage`
- Dashboard: `/admin/dashboard`

---

**Need Help?** Full documentation देखें: `TONNAGE_FORMULA_DOCUMENTATION.md`
