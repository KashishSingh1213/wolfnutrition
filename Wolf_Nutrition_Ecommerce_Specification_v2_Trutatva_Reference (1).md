# Wolf Nutrition — E-Commerce Website Specification (v2)
### Modeled on trutatva.in reference | PHP + MySQL | User Side & Admin Side

---

## 1. What We're Copying From the Reference (trutatva.in)

I reviewed the reference site. It's a well-built Ayurvedic wellness store (Shopify-based) with these
specific features worth replicating for Wolf Nutrition in our own PHP build:

| Feature on trutatva.in | Wolf Nutrition equivalent |
|---|---|
| Top announcement bar rotating offers ("Free shipping on prepaid orders", "Buy 2 @10% off") | Rotating announcement bar — admin-editable |
| Sticky header: logo, nav, search, My Account, cart with live count | Same structure |
| Search overlay with "Popular Searches" | Product search with autosuggest |
| Full-width hero image slider | Admin-managed banner slider |
| Category tile grid (image links to collection) | Category tiles: Vitality / Liver & Detox |
| Tabbed product grid ("Supplements / Skin / Hair") | Tabs by category (e.g. Vitality / Detox) |
| Product card: discount badge, strikethrough MRP, "Select Options" / "Quick Add" | Same — using our 30/60 unit variants |
| "Build a Bundle" — pick 2 products together at a combo price | Combo builder: e.g. Wolfpack + Wolftox bundle |
| "3 Core Pillars" trust strip (Clinically Tested / Ayurvedic / Holistic) | Trust strip: FSSAI Certified / 100% Ayurvedic / Veggie Capsules |
| About Us teaser + "Know more" link | Same, using your brand story |
| Star-rating / review count widget | Reviews & ratings module |
| Certificates gallery (image grid of certs) | FSSAI cert + any lab/quality certs |
| Blog section with category tag + date + excerpt | Simple blog/CMS module |
| Footer: socials, policy links, quick links | Same footer structure |
| Slide-out cart drawer (not a separate page) with note, shipping estimate, coupon field | Slide-out cart drawer |
| WhatsApp floating chat button | WhatsApp click-to-chat widget |

This is a noticeably richer feature set than a bare-bones store — it's built for **conversion**
(bundles, tiered discounts, urgency badges, trust signals). The spec below folds all of this into
your PHP build while keeping your existing FSSAI/product data.

---

## 2. USER SIDE — Full Page & Feature List

### 2.1 Global / Site-wide elements
- **Announcement bar** — rotating strip at the very top (e.g. "Free shipping on prepaid orders", "Buy 2 @10% OFF", "Buy 3 @15% OFF"). Admin can add/edit/reorder messages.
- **Sticky header**: Logo | Nav (Home / Supplements / Liver & Detox / About Us / Contact Us) | Search icon | My Account | Cart icon with live item count.
- **Search overlay**: click search icon → full overlay with input + "Popular Searches" suggestions + live results as you type.
- **Slide-out cart drawer** (not a full page): opens from the right when you click cart. Shows items, a "note for seller" field, shipping estimator, coupon code field, subtotal, and a Checkout button. Full `/cart` page still exists for direct link/SEO.
- **WhatsApp chat button** — floating bottom-right, opens WhatsApp chat with a pre-filled greeting message.
- **Footer**: social icons (Instagram/Facebook), quick links (Contact, About, Blog, Privacy Policy, Refund Policy, Shipping Policy, Terms of Service), FSSAI number.

### 2.2 Home Page
- Hero banner slider (full width, admin-managed images/links)
- Category tile grid (2–3 large clickable image tiles: e.g. "Men's Vitality", "Liver & Detox", "Shop All")
- **Tabbed product grid** — "Range of Products" section with tabs (e.g. Vitality / Detox); each tab shows a product grid with:
  - Discount badge (e.g. "-30%")
  - "Sold Out" badge when stock = 0
  - Product image, name
  - Regular price (strikethrough) + Sale price
  - "Select Options" button (if multiple variants) or "Quick Add" (adds default variant directly, shown on hover/tap)
- **Bundle Builder section** ("Build Your Wellness Stack" or similar) — pick one variant from Product A + one variant from Product B → shows combined price with savings, single "Add Bundle to Cart" button. For Wolf Nutrition this is a natural fit: **Wolfpack + Wolftox combo**.
- **Trust strip** — 3 icons + short text: "FSSAI Certified", "100% Ayurvedic Ingredients", "Veggie Capsules — No Fillers"
- **About Us teaser** — short brand story excerpt + "Know More" link to full About page
- **Reviews/ratings summary widget** — average star rating + review count, pulled from approved reviews
- **Certificates gallery** — image grid showing the FSSAI registration certificate and any other quality/lab certificates
- **Blog preview grid** — latest 4–6 articles: image, category tag, date, title, 1-line excerpt, "Read more"
- Newsletter signup strip

### 2.3 Category / Collection Page
- Same tabbed/grid product layout as home, filtered to one category
- Filters: price range, in stock only
- Sort: price low-high, high-low, newest, popularity

### 2.4 Product Detail Page
- Image gallery (multiple images, zoom on hover)
- Discount badge, "Sold Out" badge if applicable
- Variant selector (30 Capsules / 60 Capsules) — price updates live
- Regular price (strikethrough) + Sale price + "You save ₹X"
- Quantity discount messaging if applicable ("Buy 2, save 10%")
- Add to Cart / Buy Now / Add to Wishlist
- Tabs: Description | Key Benefits | Ingredients | How to Use | Reviews
- Adult-use disclaimer (Wolfpack)
- Related products / "Frequently bought together" (ties into bundle logic)
- Reviews list with star ratings, submit-a-review form (goes to admin for approval)

### 2.5 Cart (drawer + full page)
- Line items with variant, quantity stepper, remove
- Note for seller field
- Coupon code field with live validation
- Shipping estimator (enter pincode → shows charge/free shipping threshold)
- Price summary: Subtotal, Discount, Shipping, Total
- Proceed to Checkout

### 2.6 Checkout
- Guest checkout or login
- Address form (saved addresses for logged-in users)
- Payment method (UPI/Cards/Netbanking via gateway; COD optional)
- Order summary sidebar
- Place Order → payment gateway → confirmation page

### 2.7 Order Confirmation
- Order ID, items, delivery estimate, "Track Order" button
- Auto email + SMS/WhatsApp confirmation

### 2.8 My Account
- Profile (edit name/email/phone)
- Order history + status tracking with tracking number/courier
- Saved addresses (add/edit/delete/set default)
- Wishlist
- My Reviews
- Change password
- Logout

### 2.9 Authentication
- Register / Login (email or OTP-based mobile login — recommended for India)
- Forgot password (email link or OTP)

### 2.10 Content / Static Pages
- About Us (full brand story)
- Blog listing + individual blog post pages (simple CMS, admin-authored)
- Contact Us (form + phone/email + embedded map)
- FAQs
- Certificates page (larger version of the home page gallery)
- Shipping Policy, Refund Policy, Terms of Service, Privacy Policy — all admin-editable via CMS

---

## 3. ADMIN SIDE — Full Module List

Everything from the Phase 1 admin panel already delivered, **plus** these additions to support the
trutatva-style features:

### 3.1 Dashboard
*(as already built)* — revenue, orders, customers, pending orders, low-stock alerts, recent orders.

### 3.2 Product Management
*(as already built: products, variants, images, categories)* — plus:
- **Discount badge control** — set MRP/Sale price per variant (badge % auto-calculated and shown on storefront)
- **"Sold Out" auto-display** when stock_qty = 0 (no manual toggle needed, but manual override available)
- **Quick Add default variant** — mark which variant is the "default" used for one-click Quick Add

### 3.3 Bundle / Combo Management *(new)*
- Create a bundle: choose 2+ products, set a combo price or combo discount %
- Set bundle title, description, banner image
- Enable/disable bundle, set display order on homepage
- View bundle sales performance

### 3.4 Announcement Bar Management *(new)*
- Add/edit/delete rotating announcement messages
- Set display order, active/inactive toggle, optional link

### 3.5 Category Management
*(as already built)*

### 3.6 Order Management
*(as already built)* — order list/filter, detail view, status + tracking updates, invoice.

### 3.7 Customer Management
*(as already built)* — list, block/unblock, order history, spend.

### 3.8 Coupon & Quantity-Discount Management
- Coupon codes *(as already built: % / flat, min order, expiry, usage limit)*
- **New: Quantity-tier discounts** — e.g. "Buy 2 get 10% off", "Buy 3 get 15% off" applied automatically at cart level (no code needed), configurable per product or store-wide

### 3.9 Reviews & Ratings Management *(new)*
- Approve/reject submitted reviews before they appear on the product page
- View average rating per product
- Feature/pin selected reviews

### 3.10 Blog / Content Management *(new)*
- Create/edit/delete blog posts (title, category tag, cover image, body — WYSIWYG editor, publish date)
- Manage static policy pages (Shipping, Refund, Terms, Privacy) via the same WYSIWYG editor

### 3.11 Certificates Gallery Management *(new)*
- Upload/reorder/remove certificate images shown on the home page and certificates page (FSSAI cert included by default)

### 3.12 Banner & Homepage Section Management
- Hero slider images/links
- Category tile images/links
- Trust-strip icons/text (3 pillars)
- About Us teaser text + image

### 3.13 WhatsApp Widget Settings *(new)*
- Set WhatsApp business number
- Set default greeting message
- Enable/disable the floating button

### 3.14 Returns & Refunds
*(as already built)*

### 3.15 Shipping & Tax Settings
*(as already built)* — plus pincode-based shipping estimator config for the cart drawer.

### 3.16 Reports
*(as already built)* — sales report + CSV export. Add: bundle performance, top-rated products, blog traffic (if analytics integrated).

### 3.17 Admin Users & Roles
*(as already built)*

---

## 4. Updated Database Additions

On top of the schema already delivered (`database/wolf_nutrition.sql`), these new tables support the
trutatva-style features:

```
bundles              (id, title, description, banner_image, combo_price, discount_percent, status, display_order)
bundle_items         (id, bundle_id, product_id)
announcements        (id, message, link, display_order, status)
quantity_discounts   (id, product_id /*nullable = store-wide*/, min_qty, discount_percent, status)
blog_posts           (id, title, slug, category_tag, cover_image, body, status, published_at)
cms_pages            (id, slug /*shipping-policy, refund-policy etc*/, title, body, updated_at)
certificates         (id, image_url, title, display_order, status)
whatsapp_settings    (id, phone_number, greeting_message, status)
```

`reviews` table already exists in the current schema and covers 3.9 directly.

---

## 5. Build Sequence (Updated)

| Phase | Deliverable | Status |
|---|---|---|
| 1 | Database + Admin Panel (products, categories, orders, customers, coupons, reports) | ✅ **Delivered** |
| 2 | Admin additions: bundles, announcement bar, quantity discounts, reviews moderation, blog/CMS, certificates gallery, WhatsApp settings | Next |
| 3 | User-side storefront: home (with all sections above), category pages, product detail, cart drawer, checkout | After Phase 2 |
| 4 | My Account, order tracking, reviews submission, blog front-end | After Phase 3 |
| 5 | Payment gateway integration, testing, go-live | Final |

---

## 6. Next Step

I'd suggest we build **Phase 2 (the admin additions above)** next, directly on top of the admin panel
you already have — that keeps everything in one consistent codebase before we start the customer-facing
storefront. Let me know if you want me to proceed with that, or if you'd rather jump straight to the
storefront first and add bundles/blog/announcements later.
