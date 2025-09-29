# DockBloxx Product Percent Coupons

### 🚀 Overview

This plugin extends WooCommerce coupons with **advanced discounting options** designed for headless e-commerce workflows. It adds new fields to the WooCommerce coupon editor, stores them as meta data, and exposes them via the REST API for frontend handling (Next.js, React, etc.).

### ✨ Features

* **Per-product percentage discount**

  * Enter any value from 1–100
  * `100%` makes the product free

* **Restrict coupon by email**

  * Add one or more allowed email addresses (comma or newline separated)
  * Coupon will only validate for these users

* **REST API support**

  * Custom fields are included in WooCommerce’s `/coupons` REST responses
  * Ready for frontend validation + discount calculation in headless apps

* **Backward compatible**

  * Still supports normal WooCommerce “Fixed product discount” logic if the custom fields are not set

### 🛠 Usage

1. Go to **WooCommerce → Coupons**

2. Create or edit a coupon

3. Select **Fixed product discount** as the discount type

4. Fill in the new fields:

   * **Discount Percentage (per product)** → e.g. `50` for 50% off
   * **Allowed Emails** → one or more emails, separated by commas or newlines

5. Save the coupon — the data will appear in the WooCommerce REST API response under `meta_data`.

### 🔍 Example REST API Output

```json
{
  "id": 13491,
  "code": "MOOSE10",
  "discount_type": "fixed_product",
  "amount": "0.00",
  "meta_data": [
    {
      "key": "_dockbloxx_discount_percent_per_product",
      "value": "50"
    },
    {
      "key": "_dockbloxx_allowed_emails",
      "value": "moose@email.com,mical@email.com"
    }
  ]
}
```

### 📦 Installation

1. Zip the plugin folder
2. Upload via **WordPress → Plugins → Add New**
3. Activate

### 🧪 Testing

* Add eligible product(s) to cart
* Apply coupon in frontend (Next.js or Woo checkout)
* Validate discount + email restriction on the frontend

---

👉 Tomorrow, we’ll handle the **Next.js frontend integration**:

* Pull these meta fields via REST
* Apply % discount logic client-side
* Enforce allowed email restriction
* Pass the final, validated totals to `/orders`

---

