# Tonnage Formula System - Complete Documentation

## 📋 Overview (अवलोकन)

यह system Set-based Tonnage Formula Management के लिए बनाया गया है। हर Set के अपने tonnage formulas होते हैं जिनमें range-based penalty और bonus calculations होते हैं।

## 🗂️ Database Structure (डेटाबेस संरचना)

### 1. Set Master Table (`set_master`)
```
- id (Primary Key)
- set_name (Set का नाम, जैसे: "Set A", "Set B")
- description (Set की description - optional)
- created_by, updated_by, deleted_by
- created_at, updated_at, deleted_at
```

### 2. Tonnage Table (`tonnage`)
```
- id (Primary Key)
- set_id (Foreign Key → set_master.id)
- min (Minimum range value - REQUIRED)
- max (Maximum range value - REQUIRED)
- penalty_type (VARCHAR: 'percentage' या 'fixed' - optional)
- penalty_value (DECIMAL: Penalty की value - default: 0)
- bonus_type (VARCHAR: 'percentage' या 'fixed' - optional)
- bonus_value (DECIMAL: Bonus की value - default: 0)
- weight (VARCHAR: Display text, जैसे: "<35MT" - optional)
- price (DECIMAL: Base price - optional)
- created_by, updated_by, deleted_by
- created_at, updated_at, deleted_at
```

## 🚀 Setup Instructions (सेटअप निर्देश)

### Step 1: Database Migration Run करें

```bash
cd C:\xampp\htdocs\transport
php spark migrate
```

यह command निम्नलिखित करेगी:
- `set_master` table create करेगी
- `tonnage` table में `set_id` column add करेगी
- `tonnage` table में `min`, `max`, `penalty_type`, `penalty_value`, `bonus_type`, `bonus_value` columns add करेगी

### Step 2: Verify Migration

Database में check करें कि:
- `set_master` table exist करता है
- `tonnage` table में नए columns add हो गए हैं

## 📖 How to Use (कैसे उपयोग करें)

### Part 1: Set Master बनाना

1. **Set Master Page पर जाएं:**
   - URL: `/admin/set_master`
   - या Sidebar में: "Do Registration" → "Set Master"

2. **नया Set बनाएं:**
   - Set Name: जैसे "Set A", "Set B", "Standard Set"
   - Description (Optional): Set की details
   - Submit button click करें

3. **Set Edit/Delete:**
   - Edit button से Set edit करें
   - Delete button से Set delete करें (सभी associated tonnage formulas भी delete होंगे)

### Part 2: Tonnage Formula Add करना

1. **Tonnage Page पर जाएं:**
   - URL: `/admin/tonnage`
   - या Sidebar में: "Do Registration" → "Tonnage"

2. **Set Select करें:**
   - Dropdown से Set select करें
   - या Set Master page से "View Tonnage" button click करें

3. **Tonnage Formula Add करें:**

   **Required Fields:**
   - **Min (Range):** Minimum tonnage value (जैसे: 0, 35)
   - **Max (Range):** Maximum tonnage value (जैसे: 34.99, 35.99)

   **Optional Fields:**
   - **Penalty Type:** 
     - `percentage` - Percentage based penalty
     - `fixed` - Fixed amount penalty
     - Leave empty for no penalty
   
   - **Penalty Value:** Penalty की value (default: 0)
   
   - **Bonus Type:**
     - `percentage` - Percentage based bonus
     - `fixed` - Fixed amount bonus
     - Leave empty for no bonus
   
   - **Bonus Value:** Bonus की value (default: 0)
   
   - **Weight (Display):** Display text (जैसे: "<35MT", "35-36MT")
   
   - **Price (Base Rate):** Base price (optional)

4. **Submit करें:**
   - Submit button click करें
   - Formula उस Set के अंदर save हो जाएगा

### Part 3: Example Formulas (उदाहरण)

#### Example 1: Penalty Formula
```
Set: Set A
Min: 0
Max: 34.99
Penalty Type: percentage
Penalty Value: 5
Bonus Type: fixed
Bonus Value: 0
Weight: <35MT
```

**Meaning:** 0 से 34.99 tonnage range में 5% penalty लगेगा, कोई bonus नहीं।

#### Example 2: Bonus Formula
```
Set: Set A
Min: 35
Max: 35.99
Penalty Type: fixed
Penalty Value: 0
Bonus Type: percentage
Bonus Value: 10
Weight: 35-36MT
```

**Meaning:** 35 से 35.99 tonnage range में 10% bonus मिलेगा, कोई penalty नहीं।

#### Example 3: High Bonus Formula
```
Set: Set A
Min: 36
Max: 1000
Penalty Type: fixed
Penalty Value: 0
Bonus Type: percentage
Bonus Value: 20
Weight: >36MT
```

**Meaning:** 36 से 1000 tonnage range में 20% bonus मिलेगा।

## 🔄 Workflow (कार्यप्रवाह)

```
1. Set Master बनाएं
   ↓
2. Tonnage Page पर जाएं
   ↓
3. Set Select करें
   ↓
4. Min/Max Range define करें
   ↓
5. Penalty/Bonus settings add करें
   ↓
6. Submit करें
   ↓
7. Formula Set के अंदर save हो जाएगा
```

## 📊 Data Flow (डेटा प्रवाह)

### Insert Flow:
```
User Input → Validation → Controller → Database
```

### View Flow:
```
Database → Model → Controller → View → User
```

### Edit Flow:
```
User clicks Edit → AJAX call → Get data → Populate form → User edits → Update → Database
```

## 🎯 Key Features (मुख्य विशेषताएं)

1. **Set-based Organization:** हर Set के अपने formulas
2. **Range-based Calculation:** Min/Max range के आधार पर
3. **Flexible Penalty/Bonus:** Percentage या Fixed दोनों support
4. **Cascade Delete:** Set delete करने पर सभी formulas automatically delete
5. **Soft Delete:** Records permanently delete नहीं होते, soft delete होते हैं

## 🔧 Technical Details (तकनीकी विवरण)

### Files Modified/Created:

**Migrations:**
- `2026-01-22-100000_CreateSetTable.php` - Set master table
- `2026-01-22-100100_AddSetIdToTonnageTable.php` - Set ID foreign key
- `2026-01-22-100200_AddTonnageFormulaFields.php` - Formula fields

**Controller:**
- `app/Controllers/Admin.php` - Set और Tonnage functions

**Model:**
- `app/Models/AdminModel.php` - Set और Tonnage queries

**Views:**
- `app/Views/admin/set_master_vw.php` - Set master page
- `app/Views/admin/tonnage_vw.php` - Tonnage formula page
- `app/Views/admin/mainsidebar.php` - Menu updates

### API Endpoints:

**Set Master:**
- `GET /admin/set_master` - List all sets
- `POST /admin/insert_set` - Create new set
- `POST /admin/update_set` - Update set
- `POST /admin/delete_set` - Delete set
- `POST /admin/edit_set` - Get set data (AJAX)

**Tonnage:**
- `GET /admin/tonnage?set_id=X` - List tonnage by set
- `POST /admin/insert_tonnage` - Create tonnage formula
- `POST /admin/update_tonnage` - Update tonnage formula
- `POST /admin/delete_tonnage` - Delete tonnage formula
- `POST /admin/edit_tonnage` - Get tonnage data (AJAX)

## ⚠️ Important Notes (महत्वपूर्ण नोट्स)

1. **Set Selection Required:** Tonnage formula add करने से पहले Set select करना जरूरी है
2. **Min/Max Required:** Min और Max values required हैं
3. **Range Validation:** Max value Min value से बड़ी होनी चाहिए (client-side validation add कर सकते हैं)
4. **Foreign Key Constraint:** Set delete करने पर CASCADE delete होगा
5. **Data Integrity:** Existing tonnage records में set_id NULL हो सकता है, उन्हें manually assign करना होगा

## 🐛 Troubleshooting (समस्या निवारण)

### Issue: "Table 'set_master' doesn't exist"
**Solution:** Migration run करें:
```bash
php spark migrate
```

### Issue: "Foreign key constraint fails"
**Solution:** पहले Set Master में Set बनाएं, फिर Tonnage add करें

### Issue: "Tonnage not showing"
**Solution:** 
- Check करें कि Set select किया गया है
- Database में set_id properly set है या नहीं

## 📝 Future Enhancements (भविष्य के सुधार)

1. Range overlap validation
2. Formula calculation preview
3. Bulk import/export
4. Formula history/versioning
5. Advanced search and filtering

## 📞 Support

किसी भी issue के लिए development team से contact करें।

---

**Last Updated:** 2026-01-22
**Version:** 1.0
