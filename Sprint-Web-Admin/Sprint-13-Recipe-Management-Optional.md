# Web Admin / Owner Dashboard Sprint 13

# Recipe Management (Optional)

## Status

Completed

---

# Tujuan Sprint

Membangun modul **Recipe Management** untuk membantu Owner mengelola komposisi bahan baku pada setiap menu dan menghitung estimasi biaya produksi.

Sprint ini bersifat **optional** karena sistem restoran tetap dapat berjalan tanpa recipe.

Namun jika digunakan, modul ini memberikan kemampuan:

- Mengetahui kebutuhan bahan setiap menu
- Menghitung estimasi modal menu
- Menghitung food cost percentage
- Mengetahui margin setiap menu
- Mendukung analisis profit yang lebih akurat

---

# 1. Business Context

Pada restoran seafood, harga bahan baku dapat berubah setiap hari.

Contoh:

Menu:

```text
Cumi Asam Manis
```

Tanpa Recipe:

```text
Penjualan

↓

Revenue

↓

Tidak tahu modal sebenarnya
```

---

Dengan Recipe:

```text
Cumi Asam Manis

↓

Cumi 0.5 kg

↓

Bumbu 0.1 kg

↓

Saus 0.2 liter

↓

Total Cost
```

---

Owner dapat mengetahui:

```text
Harga jual

-

Modal bahan

=

Estimasi keuntungan
```

---

# 2. Hubungan Dengan Modul Existing

Recipe Management menggunakan:

```text
Menu Variant

+

Ingredient

+

Inventory Batch Cost

+

Reporting
```

---

Flow:

```text
Menu Variant

↓

Recipe

↓

Recipe Items

↓

Ingredient Cost

↓

Food Cost Analysis
```

---

# 3. Scope Sprint

## Yang dibuat:

- Recipe List
- Create Recipe
- Update Recipe
- Recipe Detail
- Ingredient Composition
- Recipe Cost Calculation
- Food Cost Analysis

---

## Tidak termasuk:

### Automatic Stock Deduction

Tidak dibuat karena inventory saat ini masih menggunakan:

```text
menu_variant

↓

inventory_quantity

↓

stock deduction
```

---

### Recipe Versioning

Contoh:

```text
Recipe lama

↓

Recipe baru
```

Ditunda.

---

### Production Planning

Tidak masuk MVP.

---

# 4. Architecture

Flow:

```text
Owner

↓

Recipe Management

↓

Laravel Recipe Service

↓

Recipe Data

↓

Cost Calculation

↓

Dashboard Analysis
```

---

# 5. Database Requirement

Menggunakan tabel:

```text
recipes

recipe_items
```

---

## recipes

```text
id

menu_variant_id

name

description

created_by

created_at

updated_at
```

---

## recipe_items

```text
id

recipe_id

ingredient_id

quantity

unit

created_at

updated_at
```

---

# 6. Relationship

Structure:

```text
Menu Variant

hasOne

Recipe


Recipe

hasMany

Recipe Items


Recipe Item

belongsTo

Ingredient
```

---

# 7. Backend Structure

Buat:

```text
app/

├── Models/

│   ├── Recipe.php

│   └── RecipeItem.php


├── Services/

│   └── RecipeService.php


├── Http/

│   ├── Controllers/

│   │   └── RecipeController.php

│   │

│   ├── Requests/

│   │   └── RecipeRequest.php

│   │

│   └── Resources/

│       └── RecipeResource.php
```

---

# 8. Recipe Model

Recipe memiliki:

```php
menuVariant()

items()
```

---

RecipeItem memiliki:

```php
recipe()

ingredient()
```

---

# 9. Recipe Service

Buat:

```text
RecipeService.php
```

---

Method:

```php
create()

update()

delete()

calculateCost()

getDetail()
```

---

Contoh:

```php
calculateCost(
    Recipe $recipe
)
```

Menghasilkan:

```text
Ingredient Cost

+

Total Recipe Cost
```

---

# 10. Cost Calculation

Menggunakan:

```text
Inventory Batch Cost
```

---

Formula:

```text
Ingredient Quantity

×

Current Ingredient Cost

=

Ingredient Cost
```

---

Contoh:

Ingredient:

```text
Cumi
```

Quantity:

```text
0.5 kg
```

Cost:

```text
80.000/kg
```

Maka:

```text
0.5 x 80.000

=

40.000
```

---

Total:

```text
Recipe Cost

=

Jumlah semua ingredient cost
```

---

# 11. Food Cost Analysis

Formula:

```text
Food Cost %

=

Recipe Cost

/

Selling Price

×

100
```

---

Contoh:

Harga jual:

```text
100.000
```

Recipe Cost:

```text
40.000
```

Food Cost:

```text
40%
```

---

# 12. API Requirement

## Get Recipe List

```http
GET /recipes
```

---

Response:

```json
{
    "data": [
        {
            "id": 1,
            "menu_variant": "Cumi Asam Manis",
            "cost": 40000
        }
    ]
}
```

---

# Create Recipe

```http
POST /recipes
```

Request:

```json
{
    "menu_variant_id": 1,
    "items": [
        {
            "ingredient_id": 5,
            "quantity": 0.5,
            "unit": "kg"
        }
    ]
}
```

---

# Update Recipe

```http
PUT /recipes/{id}
```

---

# Delete Recipe

```http
DELETE /recipes/{id}
```

---

# 13. Frontend Structure

Buat:

```text
resources/js/admin/pages/recipe/
```

---

Struktur:

```text
recipe/

├── RecipePage.vue

├── RecipeDetailPage.vue

├── components/

│   ├── RecipeTable.vue
│   ├── RecipeFormModal.vue
│   ├── IngredientSelector.vue
│   ├── RecipeCostCard.vue
│   └── FoodCostBadge.vue

└── recipe.service.js
```

---

# 14. Recipe Store

Buat:

```text
stores/recipe.store.js
```

---

State:

```text
recipes

selectedRecipe

cost

loading

error
```

---

Action:

```text
fetchRecipes()

fetchDetail()

createRecipe()

updateRecipe()

calculateCost()
```

---

# 15. Recipe List Page

Menampilkan:

| Field      | Description  |
| ---------- | ------------ |
| Menu       | Nama menu    |
| Ingredient | Jumlah bahan |
| Cost       | Modal        |
| Food Cost  | Persentase   |
| Action     | Detail       |

---

Contoh:

```text
Cumi Asam Manis

5 Ingredients

Rp40.000

40%
```

---

# 16. Recipe Form

Component:

```text
RecipeFormModal.vue
```

---

Input:

```text
Menu Variant

Ingredient

Quantity

Unit
```

---

Contoh:

```text
Menu:

Cumi Asam Manis


Ingredient:

Cumi


Quantity:

0.5


Unit:

kg
```

---

# 17. Ingredient Selector

Component:

```text
IngredientSelector.vue
```

---

Fungsi:

- Search ingredient
- Select ingredient
- Input quantity
- Input unit

---

Validasi:

Tidak boleh:

```text
Ingredient sama dua kali
```

---

# 18. Recipe Detail

Route:

```text
/recipes/{id}
```

---

Tampilan:

```text
Recipe

↓

Ingredient List

↓

Cost Calculation

↓

Food Cost
```

---

# 19. Integration Dengan Reporting

Recipe akan digunakan oleh:

```text
Sales Reporting
```

Untuk menghitung:

```text
Revenue

-

Estimated Ingredient Cost

=

Gross Profit
```

---

Contoh:

Penjualan:

```text
10 porsi
```

Cost per porsi:

```text
40.000
```

Total cost:

```text
400.000
```

---

# 20. Permission

Recipe adalah data bisnis.

Permission:

```text
recipe.view

recipe.create

recipe.update

recipe.delete
```

---

Allowed:

```text
Owner

Admin
```

---

Tidak boleh:

```text
Kasir

Kitchen
```

---

# 21. Audit Integration

Perubahan recipe harus tercatat.

Contoh:

```text
User:

Owner


Action:

UPDATE


Module:

Recipe
```

---

Karena perubahan recipe dapat mengubah:

```text
Food Cost

Profit Calculation
```

---

# 22. Validation

## Menu Variant

Required:

```text
true
```

---

## Ingredient

Required:

```text
true
```

---

## Quantity

Rule:

```text
> 0
```

---

## Unit

Harus sesuai ingredient.

Contoh:

```text
kg

gram

liter

pcs
```

---

# 23. Decimal Handling

Recipe wajib mendukung:

```text
0.025 kg

0.500 kg

1.250 liter
```

---

Tidak menggunakan:

```text
integer
```

---

# 24. Testing Scenario

## Scenario 1

Create recipe.

Expected:

```text
Recipe tersimpan.
```

---

## Scenario 2

Tambah ingredient.

Expected:

```text
Recipe item tersimpan.
```

---

## Scenario 3

Calculate cost.

Expected:

```text
Cost sesuai inventory cost.
```

---

## Scenario 4

Update recipe.

Expected:

```text
Audit log tercatat.
```

---

## Scenario 5

User tanpa permission membuka recipe.

Expected:

```text
403 Forbidden.
```

---

# 25. Task Checklist

## Backend

- [x] Recipe Migration
- [x] Recipe Model
- [x] Recipe Item Model
- [x] Recipe Service
- [x] Recipe API
- [x] Cost Calculation

---

## Frontend

- [x] Recipe Page
- [x] Recipe Detail
- [x] Recipe Form
- [x] Ingredient Selector
- [x] Cost Card

---

## Security

- [x] Permission Recipe
- [x] Audit Integration

---

## Testing

- [x] Create Recipe
- [x] Update Recipe
- [x] Calculate Cost
- [x] Permission Check

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat membuat recipe

✅ Ingredient dapat dikaitkan dengan menu

✅ Cost recipe dapat dihitung

✅ Food Cost dapat ditampilkan

✅ Perubahan recipe tercatat

✅ Permission diterapkan

---

# Output Sprint

Hasil akhir Sprint 13:

```text
Menu Variant

↓

Recipe

↓

Ingredient Cost

↓

Food Cost Analysis

↓

Better Profit Decision
```

Setelah Sprint 13 selesai, Owner memiliki kemampuan memahami biaya produksi setiap menu dan membuat keputusan harga berdasarkan data.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 14 — Outlet Management

Fitur:

- Outlet List
- Outlet Configuration
- Outlet Staff Assignment
- Outlet Status
- Multi Outlet Preparation

```

```
