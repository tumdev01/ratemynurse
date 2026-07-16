# Changelog

บันทึกการเปลี่ยนแปลงของโปรเจกต์ RateMyNurse เรียงจากล่าสุดไปเก่าสุด เพื่อ trackback ย้อนหลังได้

## 2026-07-17

### เพิ่มเช็คเบอร์ซ้ำก่อนไปขั้นตอนถัดไป + บังคับยืนยัน OTP ก่อน login จริงหลังสมัครสมาชิก (ครอบคลุมทั้ง 3 ฟอร์ม)

**คำขอ:** (1) ระหว่างกรอกฟอร์มสมัครสมาชิกและกรอกเบอร์โทรศัพท์แล้ว ถ้ากด "ถัดไป" ต้องเช็คก่อนว่าเบอร์นี้มี
ในระบบแล้วหรือยัง กันไม่ให้กรอกฟอร์มที่เหลือทั้งหมดจนจบแล้วต้องย้อนกลับมาแก้ใหม่ (2) เมื่อสมัครสมาชิกผ่านแล้ว
ต้องให้ยืนยัน OTP ก่อนถึงจะถือว่า login เข้าระบบสมบูรณ์ (เดิม registration สำเร็จแล้ว login ทันทีโดยไม่เคย
เช็ค OTP เลย) — ผู้ใช้ยืนยันให้ครอบคลุมทั้ง 3 ฟอร์ม: NursingHome (provider), Nursing (พยาบาลรายบุคคล),
Member

**Backend (Laravel):**
- เพิ่ม `OtpController::checkPhone()` + route `POST /api/check-phone` (public เหมือน otp/request) — รับ
  `{phone}` คืน `{exists: true/false}`
- เพิ่ม `OtpService::sendOtp()` (generate OTP + ส่ง SMS ในทีเดียว) รีแฟคเตอร์ให้ใช้ซ้ำได้ทั้งตอน login เดิม
  และตอนสมัครสมาชิกสำเร็จ (ใหม่)
- **เปลี่ยนพฤติกรรมสำคัญ**: `NursingHomeController::register()`, `NursingController::store()` (ผ่าน
  `NursingApiRepository::createNurse()`), `MemberController::create()` ทั้ง 3 จุด เลิกคืน `access_token`
  ทันทีหลังสมัครสำเร็จ (atomic เหมือนเดิม ไม่กระทบ) เปลี่ยนเป็นเรียก `OtpService::sendOtp()` ส่ง OTP ไปเบอร์
  ที่สมัครแทน คืน `{otp_required: true}` — ต้องเรียก `/api/otp/verify` (endpoint เดียวกับ login OTP
  เดิม เพราะหา user จาก phone เหมือนกัน ใช้ซ้ำได้เลยไม่ต้องสร้างใหม่) เพื่อเอา token จริง

**Frontend (WordPress):**
- `rmn-services.php`: `rmn_provider_register()`, `rmn_member_register()`, `rmn_nursing_register()` เลิก
  คาดหวัง `access_token` จาก response (เดิมถ้าไม่มี token จะรายงาน error ทั้งที่จริงสมัครสำเร็จ) เปลี่ยนไปเช็ค
  `otp_required` แทน ไม่ set cookie login ทันทีอีกต่อไป ส่ง `phone` กลับไปให้ JS แทน
- `Authentication.php`: เพิ่ม helper กลาง `rmnCheckPhoneExists()` / `rmnShowRegistrationOtpModal()` ใช้ร่วม
  กันทั้ง 3 ฟอร์ม
  - `providerRegisFrm` (step 1 ของ NursingHome) และปุ่ม "ถัดไป" ของ `nursingRegisFrm` — เช็ค
    `rmnCheckPhoneExists()` ก่อนสลับ tab ถ้าซ้ำแสดง toast แจ้งเตือน ไม่ไปขั้นตอนถัดไป
  - `memberRegisFrm` (ฟอร์มเดียวจบ ไม่มีหลาย step) — เช็คตอน blur ช่องเบอร์โทรทันที แสดง error inline แทนรอ
    submit สุดท้าย
  - ทั้ง 3 ฟอร์ม: หลังสมัครสำเร็จ (`otp_required`) เปิด modal ยืนยัน OTP (ใช้ modal เดียวกับตอน login
    `#otpConfirm`) ก่อนแสดง popup "สมัครสำเร็จ" — แก้ `confirmBtn` handler เดิมให้เช็ค
    `window._rmnOtpVerifiedCallback` แทนการ `location.reload()` แบบเดิมเสมอ (login flow ปกติยังทำงาน
    เหมือนเดิมทุกอย่าง เพราะไม่มี callback ก็ fallback ไป reload ตามเดิม)

**ทดสอบแล้ว:** curl ตรงเข้า Laravel local ครบวงจร (check-phone, register ไม่คืน token, OTP ถูกสร้างจริงใน
DB, verify OTP สำเร็จได้ access_token) และผ่าน WordPress `admin-ajax.php` จริงสำหรับ `provider_register` +
`verify_otp` ครบวงจรสำเร็จ (ลบข้อมูลทดสอบออกหมดแล้ว) **ยังไม่ได้ทดสอบผ่านฟอร์มจริงในเบราว์เซอร์แบบเต็ม
ขั้นตอน** — ควรทดสอบทั้ง 3 ฟอร์มก่อน deploy จริง

**เพิ่มเติม (ตามคำขอ):** ตอนกด "ถัดไป" ของ providerRegisFrm/nursingRegisFrm ระหว่างรอผล
`rmnCheckPhoneExists()` เพิ่มการแสดง loading overlay เต็มจอ (ใช้ `lockAuthUI()`/`unlockAuthUI()` ตัว
เดียวกับที่ใช้ตอนรอ OTP request/verify อยู่แล้ว — reuse ของเดิมเพื่อความสม่ำเสมอ) ให้ผู้ใช้เห็นว่ากำลังตรวจสอบ
อยู่ ไม่ใช่ค้าง ก่อนจะอนุญาตให้ไปขั้นตอนถัดไปได้

### แก้ 2 บั๊กที่เจอระหว่างทดสอบจริง: cache ค้างของ getCurrentUser() + favorites() relation หายจาก NursingHomeProfile

**บั๊ก A — เข้า login OTP ผ่านแล้วยังไม่แสดงข้อมูลสมาชิกจนกว่าจะ refresh**: แก้ `location.reload()` ออกไปรอบ
ก่อนแล้ว แต่ลืมว่า `RMN_Utils.getCurrentUser()` แคช **Promise** ผลลัพธ์ไว้ที่ module-scope
(`_rmnCurrentUserPromise`) ตลอดอายุของหน้า — ถ้าหน้าเว็บเคยเรียก `getCurrentUser()` ไปแล้วตอนยังไม่ login
(ได้ผลลัพธ์ `null`) แล้วเรียก `updateUserUI()` ซ้ำหลัง login สำเร็จโดยไม่รีเซ็ต cache ก่อน จะได้ผลลัพธ์
`null` เดิมซ้ำอยู่ดี ทั้งที่ login จริงแล้ว แก้โดยเพิ่ม `RMN_Utils.invalidateCurrentUserCache()`
(รีเซ็ต `_rmnCurrentUserPromise = null`) เรียกก่อน `updateUserUI()` ทุกจุดที่เคยแทน `location.reload()`
ไว้ (login ปกติ + 3 registration flow)

**บั๊ก B — `/api/me` error 500 จริงระหว่างทดสอบ (`Call to undefined method
App\Models\NursingHomeProfile::favorites()`)**: ตอนแก้ `NursingHomeResource` รอบก่อนให้เรียก
`profile->isFavoritedBy()` ไปเจอว่า method นี้เรียก `$this->favorites()` ภายใน แต่
`NursingHomeProfile` model **ไม่มี** relation `favorites()` เลย (บั๊กที่มีอยู่ก่อนแล้ว แค่ไม่เคยถูกเรียกจริง
เพราะ resource เดิมพังจนไม่ถึงจุดนี้) เทียบกับ `NursingProfile` ที่มี `favorites()` (morphMany ไป
`Favorite::class`) อยู่แล้ว เพิ่ม relation เดียวกันให้ `NursingHomeProfile` — ทดสอบยืนยันผ่าน `/api/me`
จริงกับ user ที่เพิ่ง error สำเร็จแล้ว (คืนข้อมูลครบ: firstname, plan, profiles พร้อม is_favorite)

### แก้ header/nav ไม่แสดงชื่อ+แพ็กเกจหลัง login (NURSING_HOME) + ไม่ต้อง reload หน้าหลัง OTP ผ่าน + logout เร็วขึ้น

**บั๊ก 1 — ชื่อว่าง + แพ็กเกจ "undefined" เฉพาะ user_type = NURSING_HOME**: root cause คือ
`NursingHomeResource.php` (backend) ไม่มี `firstname`/`lastname`/`plan` เลย และคืน `profile` (เอกพจน์) แทน
`profiles` (พหูพจน์ ตามที่ route `/api/me` eager-load ไว้และ frontend JS อ่าน `user.profiles[0]`) — ต่างจาก
`MemberResource`/`NursingResource` ที่มีครบถูกต้องอยู่แล้ว เขียนใหม่ให้มี `firstname`, `lastname`, `plan`,
`plan_start`, `profiles` (array ของทุกสาขา), `notifications`/`read_notifications`/`unread_notifications` —
ตาม domain model ที่ NursingHome หนึ่ง user มีได้หลายสาขา (`nursing_home_profiles.user_id` ชี้กลับมาที่
user) ต่างจาก Nursing/Member ที่มีแค่ 1 profile ต่อ user เสมอ ปรับ `displayName` ฝั่ง frontend
(`updateUserUI()`) ให้: มีสาขาเดียว → ใช้ชื่อสาขา (`profiles[0].name`) เหมือน Nursing ทั่วไป, มีหลายสาขา →
ใช้ชื่อเจ้าของบัญชี (`firstname`+`lastname`) แทน เพราะไม่มีสาขาเดียวที่เป็นตัวแทนได้ชัดเจน — เพิ่ม fallback
"ยังไม่มีแพ็กเกจ" กันแสดง literal "undefined"/"null" ตอน `user.plan` ว่าง

**บั๊ก 2 — ต้อง refresh หน้าถึงจะเห็นสถานะ login ใหม่**: `updateUserUI()` เดิม insert user-menu ใหม่ทุกครั้งที่
เรียกโดยไม่ลบอันเก่าออกก่อน (ไม่ idempotent) เรียกซ้ำจะเห็นข้อมูลซ้อนกัน 2 ชุด — นี่คือเหตุผลที่โค้ดเดิมใช้
`location.reload()` หลัง login/OTP verify สำเร็จ แก้โดยให้ลบ `#rmn-user-menu` เก่าออกก่อนสร้างใหม่เสมอ
(idempotent) แล้ว `window.updateUserUI = updateUserUI` expose เป็น global เรียกจากที่อื่นได้ — แทนที่
`location.reload()` ทั้งใน login flow ปกติ (ปิด modal + เรียก `updateUserUI()` แทน) และใน 3
registration-success callback (provider/nursing/member) ที่เดิมก็ไม่เคยรีเฟรช header เลยหลัง OTP ผ่าน

**บั๊ก 3 (UX request) — logout ช้าเพราะรอ toast 2 วินาทีก่อน redirect**: ตัด toast "กำลังออกจากระบบ" +
`timer: 2000` ออก redirect ทันทีที่ API สำเร็จแทน (หน้ากำลังจะเปลี่ยนอยู่แล้ว โชว์ toast ค้างไว้ไม่มีประโยชน์)

ทดสอบ: ยิง `NursingHomeResource` ตรงๆ ผ่าน tinker (จำลอง eager-load ตาม route `/api/me` เป๊ะ) ยืนยันว่า
`firstname`/`lastname`/`plan`/`profiles` ออกมาถูกต้องครบแล้ว — **ยังไม่ได้ทดสอบผ่านเบราว์เซอร์จริงสำหรับ
ทั้ง 3 บั๊กนี้**

### เพิ่ม UX เมื่อเจอเบอร์ซ้ำ: แสดง error message ใต้ช่องกรอก + highlight ขอบสีแดง

เดิมตอนเจอเบอร์ซ้ำ (providerRegisFrm/nursingRegisFrm) แสดงแค่ toast มุมขวาบน ไม่มี inline feedback ที่ตัว
input เลย เพิ่ม `rmnShowPhoneDuplicateError()`/`rmnClearPhoneDuplicateError()` เป็น helper กลาง ใช้ pattern
เดียวกับ error อื่นๆ ในฟอร์มนี้ (`label.error` เป็น sibling ถัดจาก input) — เติมข้อความ "เบอร์โทรศัพท์นี้มี
ผู้ใช้งานแล้ว" + เพิ่ม class `border-red-500` ที่ input ตอนเจอซ้ำ และล้างออกอัตโนมัติทันทีที่ผู้ใช้แก้เบอร์ใหม่
(hook เข้ากับ `input` event listener เดิมที่มีอยู่แล้ว) — Member ฟอร์มมี behavior นี้อยู่แล้วจากรอบก่อน
(ผ่าน `validateForm()`) ไม่ต้องแก้เพิ่ม

### แก้ /api/check-phone กับ /api/otp/request ยิงตรงจาก browser ไปหา production เสมอ แม้ทดสอบ local

`local-dev-overrides.php`'s `pre_http_request` filter ดักได้แค่ request ที่ยิงจากฝั่ง PHP server
(`wp_remote_post`) เท่านั้น — แต่ `/api/check-phone` (ใหม่) และ `/api/otp/request` (เดิม) ถูกเรียกตรงจาก
**browser** (axios) ไปหา `services.ratemynurse.org` เลย ไม่ผ่าน WordPress เลย จึงไม่โดน filter ดักไว้ —
ทดสอบ local จึงเผลอยิงไป production เสมอ

แก้โดยย้าย URL เหล่านี้เข้า `js/rmn-config.js` (`RMN_CONFIG.api.baseUrl`) จุดเดียว แทนการ hardcode ใน
`Authentication.php` — working copy ตั้งเป็น `http://localhost:9000/api` (port ที่ docker-compose expose
ให้ container `rmn_laravel_backend` โดยตรง เรียกจาก browser บนเครื่องเดียวกันได้เลย ไม่ต้องแก้ hosts file)
ส่วน deploy copy คงเป็น `https://services.ratemynurse.org/api` เหมือนเดิม — **ไฟล์นี้ตั้งใจให้ต่างกันตรง
บรรทัด baseUrl บรรทัดเดียวระหว่าง working copy กับ deploy copy ตลอดไป ห้าม sync ทับกันทั้งไฟล์**

ทดสอบยืนยันแล้วว่า `http://localhost:9000/api/check-phone` เรียกจาก host machine ได้จริง (จำลอง browser)

### พบ production error `RMN_Utils is not defined` — สงสัยว่าไฟล์ใหม่บางไฟล์ยังไม่เคยอัปโหลดขึ้นจริง

ตรวจสอบ `Authentication.php`/`job-post.php` (ทั้ง working copy และ deploy copy) แล้วพบว่าทุกจุดที่เรียก
`RMN_Utils.*` มีการ guard ด้วย `DOMContentLoaded`/`window.load` ถูกต้องอยู่แล้ว (ไม่ใช่บั๊ก race condition
ในโค้ด) จึงสงสัยว่าสาเหตุจริงคือไฟล์ `js/rmn-utils.js` (ไฟล์ใหม่จากรอบก่อนหน้า ไม่เคยมีอยู่บนเซิร์ฟเวอร์เลย)
ยังไม่ถูกอัปโหลดขึ้นจริงในรอบ deploy ก่อนหน้า — ตรวจสอบ `deploy/wp-content/plugins/rmn-services/js/rmn-utils.js`
มีอยู่ครบและตรงกับ working copy (diff ต่างกันแค่ line ending) ยืนยันว่าไฟล์นี้พร้อมสำหรับรอบ deploy ถัดไป —
ดู `deploy/FILES_TO_UPLOAD.txt` หัวข้อ "สรุปล่าสุด" สำหรับรายการไฟล์ที่ต้องอัปโหลดครบ (ทำเครื่องหมาย
[ไฟล์ใหม่] ไว้ชัดเจน)

### แก้ user.status ไม่ตรงกันตอนสมัคร NursingHome + error toast โผล่หลัง modal + ขยาย OTP เป็น 90 วินาที

**พบระหว่างผู้ใช้ทดสอบสมัครสมาชิก NursingHome จริง**

- **`user.status` กับ `nursing_home_profile.status` ไม่ตรงกัน**
  (`backend/laravel/app/Http/Controllers/API/NursingHomeController.php`, method `register()`) — สมัคร
  แล้ว `user.status` ถูก set เป็น `0` (inactive) แต่ `nursing_home_profile.status` default เป็น `1`
  (active) ทำให้ user มองว่ายัง inactive ทั้งที่ profile บอกว่า active แล้ว แก้เป็น `status => 1` ให้ตรง
  กันตั้งแต่สมัครสำเร็จ — ทดสอบยืนยันผ่าน local แล้วว่าทั้งสอง status ตรงกันเป็น `1` ทั้งคู่
- **Error toast โผล่ "หลัง" modal สมัครสมาชิก มองไม่เห็น** — root cause คือ SweetAlert2 (ใช้แสดง
  toast/alert ทั้งระบบ) มี z-index เริ่มต้นของตัวเองต่ำกว่า z-index ของ modal/dropdown ที่มีอยู่แล้วในระบบ
  (`.authen-loading-overlay` 100000, `.ts-dropdown` 999999) แก้โดยเพิ่ม inline style ใหม่ใน
  `rmn-services.php` บังคับ `.swal2-container { z-index: 999999999 !important; }` ให้ลอยอยู่บนสุดเสมอ
  ไม่ว่าจะมี modal อะไรเปิดอยู่ก็ตาม
- **ขยายเวลาหมดอายุ OTP จาก 60 วินาที เป็น 90 วินาที** (ต่อจากรอบก่อนที่เคยขยายจาก 30 เป็น 60) —
  แก้ทั้ง `backend/laravel/app/Http/Controllers/API/OtpController.php` (ค่าที่ส่งจริงตอนเรียก
  `generate()` และข้อความ SMS ที่แจ้งเวลาหมดอายุ ซึ่งเดิมเขียนผิดเป็น "30 วินาที" มาตั้งแต่รอบก่อนทั้งที่
  ตั้งเวลาไว้ 60 — แก้ให้ตรงกับของจริงด้วย) และ `backend/laravel/app/Services/OtpService.php` (default
  parameter `$ttl`) พร้อมปรับ countdown ฝั่ง frontend ใน `Authentication.php`
  (`data-timer="30"` → `data-timer="90"`, JS fallback `|| 30` → `|| 90`) ให้นับถอยหลังตรงกับเวลาจริงฝั่ง
  backend — ทดสอบยืนยันผ่าน tinker แล้วว่า record OTP หมดอายุห่างจาก `now()` 90 วินาทีจริง

### สำคัญมาก: ajax proxy ฝั่ง WordPress ยิงไป production จริงเสมอ แม้ทดสอบจาก local

**พบระหว่างช่วยผู้ใช้ debug ตอนทดสอบสมัคร NursingHome บน local** — ajax proxy ทุกตัวใน `rmn-services.php`
(รวม `rmn_provider_register()`) hardcode URL เป็น `https://services.ratemynurse.org/...` ตรงๆ ไม่ว่า
WordPress จะรันอยู่ที่ไหน แปลว่า **การทดสอบผ่านหน้าเว็บ local (`localhost:3000`) ที่ทำมาตลอดเซสชันนี้ ไม่มี
ทางเทส backend fix ที่แก้ไปได้เลย** เพราะ request จริงวิ่งไปที่ production เสมอ (ยืนยันด้วย
`curl -w "%{remote_ip}"` จาก container WordPress ได้ IP production `128.199.140.164` ตรงๆ)

- เดิมมี Caddy proxy + docker network alias (`docker-compose.yml`) ตั้งใจให้ resolve
  `services.ratemynurse.org` เข้า container local เอง แต่ **ทดสอบแล้วไม่ทำงาน** (network alias ไม่ resolve
  ตามที่ตั้งใจในเครื่องนี้ ไม่ได้สืบสาเหตุลึกกว่านี้เพราะมีทางแก้ที่ไม่ต้องพึ่ง docker DNS)
- แก้โดยเพิ่ม filter `pre_http_request` ใน mu-plugin ที่มีอยู่แล้ว
  (`frontend/wordpress/wp-content/mu-plugins/local-dev-overrides.php`, ไฟล์นี้ local-only ไม่ sync ขึ้น
  production) ให้ rewrite request ที่ยิงไป `services.ratemynurse.org` ให้ไปที่
  `http://rmn_laravel_backend` (docker container name, resolve ผ่าน docker DNS ได้เสมอ ยืนยันแล้ว) แทน
  ก่อนที่ WordPress จะยิง request จริง
- ทดสอบยืนยันแล้ว: ยิง `provider_register` ผ่าน `admin-ajax.php` จาก local ได้ user id ต่อเนื่องจาก DB
  local (ไม่ใช่เลขที่จะเกิดบน production) พร้อม atomic response ครบถ้วนตามที่แก้ backend ไว้
- **ผลข้างเคียงที่ต้องรู้:** ระหว่างที่ยังไม่มี fix นี้ การทดสอบสมัคร NursingHome ของผู้ใช้เมื่อครู่ (email
  `01heathcare@mail.com`) **ไปสร้าง record จริงบน production แล้ว** ผ่านโค้ด backend เวอร์ชันเก่า (ที่ยังมี
  บั๊ก commit-then-fail เดิมอยู่ เพราะยังไม่ได้ deploy ไปแก้) — ควรพิจารณาไปเช็ค/ลบข้อมูลทดสอบนี้บน production
  ทีหลัง ถ้าไม่ต้องการให้ค้างอยู่

### แก้บั๊ก Tom Select ที่เจอระหว่างทดสอบจริง (ต่อจากรอบ migrate select2 → Tom Select)

- **CSS ซ้อนกัน 2 ชั้น**: Tom Select copy class เดิมของ `<select>` (`border rounded-lg px-3 py-2`) ไปใส่ใน
  `.ts-wrapper` (div ที่ห่อข้างนอก) โดยอัตโนมัติ ทำให้ทั้ง wrapper และ `.ts-control` ข้างในมี
  border/padding ซ้อนกัน 2 ชั้น — แก้ด้วย CSS reset `.ts-wrapper { border: none !important; padding: 0
  !important; }` ในทั้ง 3 ไฟล์ live (`rmn-services.php`, `Authentication.php`, `job-post.css`)
- **Tom Select auto-select ตัวเลือกแรกเป็น default**: จังหวัดเผลอโชว์ "กระบี่" ค้างไว้ทั้งที่ยังไม่ได้เลือก
  — แก้ด้วยเรียก `instance.clear(true)` หลังสร้าง instance เมื่อไม่มีค่าเดิมให้ seed
  (`js/rmn-location-selector.js`)
- **ปุ่ม "สมัครสมาชิก" (`#providerCreate`) ไม่มี logic enable/disable เลยตั้งแต่ต้น** (ต่างจากปุ่มอื่นในหน้า
  เดียวกันที่มี) — เพิ่ม `checkProviderProfileRequiredFields()` ดัก `change`/`input` event ของ
  `providerProfileFrm` เปิดปุ่มเมื่อกรอกครบ (address/province/district/sub_district/zipcode) — พบว่า
  `<textarea id="address">` ไม่มี `required` attribute ทั้งที่ label โชว์ `*` แก้เพิ่มด้วย
- **Tom Select ไม่ dispatch native 'change' event ที่ bubble ขึ้น form เหมือน select2 เดิม** (select2 เดิม
  ใช้ jQuery `.trigger('change')` ซึ่ง dispatch native event จริง) — เพิ่ม
  `element.dispatchEvent(new Event('change', {bubbles:true}))` เองใน Tom Select's `onChange` callback
  ทั้ง 2 method (`initDropdownFromData`/`initDropdown`) กันโค้ดอื่นที่ดัก form-level change event
  (เช่น logic enable/disable ปุ่มข้างบน) ไม่ทำงาน
- **Tom Select ไม่โหลดข้อมูลทันทีตอนเปิด dropdown ครั้งแรก** (ต่างจาก select2 เดิมที่ยิง ajax ทันทีตอนเปิด) —
  เพิ่ม `preload: "focus"` ให้ dropdown แบบ remote-search (อำเภอ/ตำบล)
- ทุกจุดแก้แล้วทั้ง working copy และ deploy (ยกเว้น mu-plugin ซึ่ง local-only อยู่แล้ว)

### เปลี่ยน: select2 → Tom Select สำหรับ dropdown จังหวัด/อำเภอ/ตำบล

**เหตุผล:** select2 ต้องพึ่ง jQuery และมีบั๊ก dropdown ลอยผิดตำแหน่งที่ต้อง hack
`dropdownParent`/`position:relative` เอง (มี comment อธิบายเหตุผลไว้ในโค้ดเดิม) อีกทั้งการค้นหาไม่ได้
filter ที่ server จริง — ดึงข้อมูลมาทั้งหมดแล้ว filter ซ้ำฝั่ง client

**ขอบเขตที่ตรวจสอบก่อนแก้:** select2 ในปลั๊กอินถูกเรียกใช้ผ่าน class `RMN_LocationSelector`
(`js/rmn-location-selector.js`) จุดเดียวเท่านั้น (ยืนยันด้วย grep ทั้งปลั๊กอิน ไม่มี `$(...).select2()`
ตรงๆ ที่อื่นเลย) — งานนี้เลยเป็นแค่ rewrite ข้างในไฟล์เดียวโดยคง public API เดิมทั้งหมด ไม่ต้องแก้จุดเรียกใช้
4 จุด (`Authentication.php`, `job-post.php`, `nursing_home_profile.js`, `nursing_profile.js`) เลย
แม้แต่บรรทัดเดียว

- `js/rmn-location-selector.js` — rewrite ข้างในให้ใช้ Tom Select แทน select2 ทั้งหมด (static-data
  dropdown สำหรับจังหวัด, remote-search dropdown ผ่าน axios สำหรับอำเภอ/ตำบล คงพฤติกรรม debounce 250ms
  + filter ฝั่ง client เดิมไว้) **ลบ workaround `dropdownParent`/`position:relative` ทิ้งไปเลย**
  เพราะ Tom Select ไม่มีปัญหา dropdown ลอยผิดตำแหน่งแบบ select2 (dropdown render ติดกับ wrapper ของ
  element เดิมโดย design)
- `rmn-services.php` — เปลี่ยนจุด enqueue จาก select2 CDN เป็น Tom Select CDN (`tom-select@2.3.1`,
  bundle `complete` มี plugin `clear_button` มาด้วยแทน `allowClear` เดิม) + แปลง inline CSS fallback
  ("กัน CDN โดนบล็อก") เป็น class ของ Tom Select
- CSS 3 ไฟล์ live: `Authentication.php`, `job-post.css`, `my-profile.php` (2 จุด) — แปลง selector จาก
  `.select2-*` เป็น `.ts-control`/`.ts-dropdown` เทียบเท่า คง spec เดิมไว้ (border-radius, height,
  font-size, z-index)
- **เจอระหว่างทาง (เฉพาะใน `deploy/`):** deploy copy ของ `Authentication.php` มี dropdown
  จังหวัด/อำเภอ/ตำบลอีกชุดที่ implement เป็น raw select2 ตรงๆ (ไม่ผ่าน `RMN_LocationSelector`,
  ฟังก์ชัน `handleSelectProvince()`/`ajaxCallDropdownOption()`) เป็นเวอร์ชันเก่าที่ยังไม่เคย refactor มาใช้
  class กลาง — เจอ HTML bug เสริมด้วย (`<select>...` ปิดด้วย `<select>` แทน `</select>` ที่ถูกต้อง)
  แก้โดยแทนที่ทั้งชุดด้วย `new RMN_LocationSelector({...})` เดียวกับ working copy (ลบฟังก์ชัน select2
  เดิมทิ้ง, แก้ HTML ให้ปิด tag ถูกต้อง) แทนที่จะเขียน Tom Select implementation คู่ขนานอีกชุด
- ทดสอบผ่าน local docker: syntax ผ่านหมด (PHP+JS), เปิดหน้าเว็บจริงยืนยัน Tom Select โหลดถูกต้อง ไม่มี
  select2 เหลือ ไม่มี PHP error, markup + script enqueue ของ `RMN_LocationSelector` ถูกต้อง — **ยังไม่ได้
  ทดสอบ interaction ในเบราว์เซอร์แบบละเอียด** (cascading select, ค้นหา, clear button) ผู้ใช้แจ้งว่าจะ
  ทดสอบรวมกับงานอื่นทีเดียวทีหลัง

### Fixed: Nursing (พยาบาลรายบุคคล) register 3 บั๊กที่ค้างไว้จากการสำรวจก่อนหน้า

**1) บั๊ก commit-then-fail แบบเดียวกับ NursingHome** — `NursingApiRepository::createNurse()` เป็น
`DB::transaction()` ที่ถูกต้องอยู่แล้ว (สร้าง `users` + `nursing_profiles` คู่กัน) แต่
`NursingController::store()` เดิมเรียก `$nursing->createToken()` **หลัง** transaction commit ไปแล้ว —
ย้าย token creation เข้าไปในทรานแซคชันเดียวกันเลย (`createNurse()` คืนค่าเป็น
`['user' => $nursing, 'token' => $token]` แทน `$nursing` เดี่ยวๆ) `store()` แค่ประกอบ response จากค่านี้
ไม่เรียก `createToken()` เองอีกต่อไป — response shape เดิมไม่เปลี่ยน (`{success, data: {user,
access_token}}`) ไม่กระทบ WP proxy ฝั่งที่อ่านอยู่แล้ว

**2) รูปถ่ายที่ฟอร์มบังคับอัปโหลดหายไปเงียบๆ** — JS แนบไฟล์มาถูกต้องอยู่แล้ว แต่ WP proxy
`rmn_nursing_register()` ส่งเป็น JSON ผ่าน `wp_remote_post()` ไฟล์เลยไม่เคยถึง backend เลย —

- เขียน `rmn_nursing_register()` ใหม่จาก `wp_remote_post()`+JSON เป็น `curl_exec()`+`CURLFile`
  (pattern เดียวกับ `nursing_profile_draft_save()` ที่มีอยู่แล้วในไฟล์เดียวกัน)
- เพิ่มการรับไฟล์ใน `NursingApiRepository::createNurse()` (ในทรานแซคชันเดียวกัน) — copy pattern การ
  อัปโหลดรูปจาก `updateProfile()` ที่มีอยู่แล้ว (move ไป `public/images`, สร้าง `Image` record
  `type=NURSING, is_cover=true`)
- เพิ่ม validation `profile_photo` ใน `NursingCreateRequest` (เดิม comment ทิ้งไว้เป็นชื่อ field ผิด
  `profile_image` — แก้เป็นชื่อจริงที่ JS ส่งมา `profile_photo`, required, image, mimes
  jpeg/jpg/png/webp, max 5MB)

**3) รายละเอียดโรคประจำตัว/ประวัติแพ้ยาหายไป** — ฟอร์มมี textarea อยู่แล้วแต่ JS ไม่เคยส่งค่า และ
**ตรวจกับ DB จริงยืนยันว่าคอลัมน์ไม่มีอยู่เลย** (ต่างจาก `care_type` ที่มีอยู่แล้วแค่ไม่ได้ต่อสาย) —

- Migration ใหม่เพิ่ม `medical_condition_detail`/`history_of_drug_allergy_detail` (text, nullable)
  เข้า `nursing_profiles` + เพิ่มเข้า `$fillable` ของ `NursingProfile` + wiring เข้า `createNurse()`
- **บั๊กเสริมที่เจอระหว่างทาง:** wrapper ของ `history_of_drug_allergy_detail` เป็น `class="hidden"`
  ตายตัว ไม่มี id ให้ toggle เลย (ต่างจาก `medical_condition_wrap` ที่ toggle ปกติ) — ผู้ใช้กด "มี" ก็ไม่
  เห็นช่องกรอกอยู่ดี เพิ่ม `id="history_of_drug_allergy_wrap"` + toggle logic ให้เหมือน
  `medical_condition` คู่กัน (ไม่งั้นต่อสาย backend ไปก็ไม่มีทางได้ข้อมูลจริงจากผู้ใช้)

**ทดสอบผ่าน local docker ครบทุกจุด:**
- `curl -F` ยิง `/api/nursing/create` ตรงๆ แนบไฟล์จริง + ข้อมูลครบ → user+profile+image ถูกสร้างครบใน
  ทรานแซคชันเดียว, ไฟล์รูปถูก copy ไปที่ `public/images/` จริง
- ทดสอบ retry โดยตั้งใจไม่แนบรูป (validation fail) → ยืนยัน 0 row ค้างใน `users` (atomic จริง)
- ทดสอบ WP-proxy layer แยกต่างหาก (จำลอง logic ของ `rmn_nursing_register()` แต่ชี้ไป Laravel container
  ใน local แทน production กันไม่ให้ไปสร้าง record จริงบน production) → ยืนยันว่า curl+CURLFile ที่ฝั่ง
  WordPress ประกอบ request ถูกต้อง ได้ response 200 พร้อม token กลับมา

**ไฟล์ที่แก้:** `database/migrations/2026_07_17_000000_add_detail_fields_to_nursing_profiles_table.php`
(ใหม่), `NursingProfile.php`, `NursingCreateRequest.php`, `NursingApiRepository.php`,
`NursingController.php`, `Authentication.php`, `rmn-services.php` — mirror เข้า `deploy/` ครบแล้ว
(deploy copy ของ `Authentication.php` เดิมเป็นเวอร์ชันเก่ากว่าที่ไม่มี feature อัปโหลดรูปเลย เลยต้อง
เพิ่ม UI/JS ส่วนอัปโหลดรูปเข้าไปทั้งชุดด้วย ไม่ใช่แค่แก้บั๊ก — ตั้งใจไม่แตะส่วนอื่นที่ deploy copy มี
implementation เก่ากว่า working copy อยู่แล้ว เช่น location selector เพราะไม่เกี่ยวกับบั๊กที่แก้รอบนี้)

### Fixed: /api/me ถูกยิงซ้ำบ่อย (ทำก่อนคิว performance optimization หลัก เพราะเป็นจุดเดียวที่ชัดเจนสุด)

จากผลตรวจสอบความช้าที่ทำไปก่อนหน้า (ดู CHANGELOG 2026-07-16 "แผนงานถัดไป: Performance Optimization")
พบว่า user ที่ login อยู่จะยิง `/api/me` มากถึง 4 ครั้งต่อการโหลดหน้าเดียว:

- **ฝั่ง server**: `AccessGuard::me()` (`includes/AccessGuard.php`) ยิง `wp_remote_get('/api/me')` แบบ
  synchronous ทุกครั้งที่โหลดหน้า (ผูกกับ `checkSubscriptionExpiry()` บน `template_redirect`) — memoize
  แค่ใน request เดียว ไม่มี cache ข้าม request เลย
- **ฝั่ง client**: มี 3 จุดที่ยิง ajax action `get_current_user` (คือ `/api/me` เดียวกัน) แบบไม่รู้จักกัน —
  desktop nav (`updateUserUI()`), mobile nav, และ `job-post.php`

**แก้โดย:**
- เพิ่ม transient cache 30 วินาทีต่อ token (`rmn_me_<hash>`) ทั้งใน `AccessGuard::me()` และ
  `rmn_get_current_user()` (`rmn-services.php`) — **ใช้ cache key เดียวกัน** ทั้ง 2 จุด เพื่อให้ฝั่ง
  server-side (เกิดก่อนเสมอ เพราะ template_redirect ทำงานก่อน JS) เขียน cache ไว้ให้ฝั่ง client-side
  มาอ่านต่อได้เลย ไม่ต้องยิง Laravel ซ้ำ
- เพิ่ม `RMN_Utils.getCurrentUser()` (`js/rmn-utils.js`) — cache เป็น promise เดียวกันไว้ตลอดอายุของหน้า
  แล้วให้ทั้ง 3 จุดใน JS เรียกผ่าน helper นี้แทนการยิง axios ตรงๆ เอง — กันยิงซ้ำกันเองภายในหน้าเดียว
  แม้ backend cache จะ hit ก็ยังลดจำนวน network round-trip ได้อีกชั้น
- ทดสอบผ่าน local docker แล้วว่า cache key ตรงกันและกลไก transient ทำงานถูกต้อง (จำลอง
  set/get transient ตรงๆ) — ยังไม่ได้ทดสอบผ่าน browser จริงกับ session login จริงเพราะ local ไม่มี
  ผู้ใช้จริงให้ทดสอบ ต้องเช็ค DevTools > Network หลัง deploy จริง
- ไฟล์ที่แก้: `includes/AccessGuard.php`, `rmn-services.php`, `js/rmn-utils.js`,
  `services/authentication/Authentication.php`, `services/board/job-post.php` — mirror เข้า `deploy/`
  ครบแล้ว (`AccessGuard.php`/`rmn-utils.js` เป็นไฟล์ใหม่ที่ไม่เคยอยู่ใน deploy/ มาก่อน เพิ่ม entry ใหม่ใน
  `deploy/FILES_TO_UPLOAD.txt` แล้ว)

## 2026-07-16

### แผนงานถัดไป: Performance Optimization (ยังไม่เริ่มทำ — ทำหลังสุด)

ตรวจสอบสาเหตุความช้าทั้งฝั่ง WordPress frontend และ Laravel backend แล้ว (ผ่าน code review เท่านั้น
ยังไม่ได้เข้าไปดู production infra จริง) **ข้อตกลง: จะเริ่มลงมือทำ optimization ทีหลังสุด (หลัง Nursing
register fix และ Tom Select) และก่อนเริ่ม ต้อง SSH เข้า DigitalOcean droplet ดูโครงสร้างจริงก่อน** (ยืนยันว่า
OPcache/php.ini/`.env` จริงบน production ตรงกับที่โค้ดใน repo บอกไว้หรือไม่) ก่อนจะไปแก้ deploy pipeline หรือ
โครงสร้าง backend

**สรุปสาเหตุที่พบ เรียงตาม impact:**

Backend (Laravel):
1. **ไม่มี OPcache เลย** (`backend/Dockerfile` ติดตั้งแค่ `pdo_mysql`, `mbstring`, `zip` ไม่เปิด opcache) —
   กระทบทุก request แบบไม่มีข้อยกเว้น (ต้อง parse/compile PHP ใหม่ทุกครั้ง)
2. **Deploy pipeline ไม่เคย cache config/route** (`.github/workflows/deploy-backend.yml` สั่ง
   `config:clear`/`route:clear` ทุกครั้งที่ deploy แต่ไม่เคยสั่ง `config:cache`/`route:cache` กลับ)
3. **Index หายจากคอลัมน์ hot path**: `rates.rateable_id/type`, `images.imageable_id/type` (ไม่มี index
   ตั้งแต่สร้างตาราง), `users.user_type`/`status` (มี global scope filter ทุก query ของ
   Nursing/NursingHome/Member แต่ไม่มี index รองรับ)
4. **Listing endpoint หลัก (nursing/nursing-home/job) ไม่มี cache เลย** — cache driver เป็น `file`,
   มีแค่ provinces/districts/contacts ที่ cache ไว้ ส่วน endpoint หลักยิง MySQL สดทุก request
5. **Filter สิ่งอำนวยความสะดวกใช้ `JSON_SEARCH` แบบ full scan** + **ค้นหาชื่อใช้ `LIKE '%...%'`**
   (wildcard นำหน้า ใช้ index ไม่ได้แม้จะมี index)
6. **Pagination logic ซ้ำซ้อน + `ORDER BY RAND()`** — จังหวัดที่มีผลลัพธ์ ≤10 รายการ รันทั้ง query
   นับจำนวน + query จริงซ้อนกัน แล้ว paginate เองด้วย PHP (`NursingHomeRepository::getNursingHomeWithZone()`
   และเทียบเท่าฝั่ง Nursing)
7. **อัปโหลดไฟล์ทำแบบ synchronous ทุกจุด** (`QUEUE_CONNECTION=sync`, ไม่มีระบบ queue เลยในโค้ดทั้งหมด) —
   ผลกระทบต่ำกว่าเพราะเกิดเฉพาะตอนแก้โปรไฟล์ ไม่ใช่หน้า public

Frontend (WordPress):
- `AccessGuard::me()` ยิง `/api/me` แบบ synchronous บล็อกทุกครั้งที่โหลดหน้าสำหรับ user ที่ login อยู่
  (ผูกกับ `template_redirect`) ซ้ำเติมด้วย JS อีกอย่างน้อย 3 จุดที่ยิง endpoint เดียวกันซ้ำอีกแบบไม่รู้จักกัน
  — หน้าเดียวอาจยิง `/api/me` ได้ถึง 4 ครั้ง ไม่มี cache ฝั่ง backend เลย
- 3 จุดใน `rmn-services.php` ไม่ตั้ง timeout ให้ curl เลย (โดยเฉพาะปุ่ม favorite ที่กดบ่อยสุด) — ถ้า backend
  ค้าง PHP-FPM worker จะค้างไม่จำกัดเวลา
- JS ของปลั๊กอินเองไม่ minify, SweetAlert2 โหลดซ้ำ 3 รอบในหน้า job-detail, รูป ~60% ไม่มี `loading="lazy"`
- Cache-Control ที่ปิด cache ไว้สำหรับ user ที่ login (กัน session leak) **ทำถูกต้องแล้ว** ไม่กระทบผู้เข้าชม
  ทั่วไป ไม่ใช่สาเหตุหลักของความช้า

### เพิ่ม: care_type (ประเภทผู้ให้บริการ RN/PN/NA/CG/MAID) ให้พยาบาลกำหนดเองได้

**พบระหว่างตรวจสอบ:** validation rule ของ `care_type` มีอยู่แล้วใน `NursingCreateRequest`/
`NursingUpdateRequest` (`nullable, in:RN,PN,NA,CG,MAID`) แต่ไม่มีใครใช้จริง — ทั้ง
`NursingApiRepository::createNurse()` (endpoint สมัครสมาชิก) และ `updateProfile()` (endpoint แก้ไข
โปรไฟล์ตัวเอง) ไม่เคยอ่านค่านี้ไปเก็บลง `NursingProfile` เลย ทั้งที่ column/`$fillable` รองรับอยู่แล้ว —
เดิมมีแค่แอดมินฝั่ง Blade panel เท่านั้นที่กำหนดค่านี้ได้ พยาบาลที่สมัครเองไม่มีทางตั้งค่าของตัวเองได้เลย

- **Backend:** เพิ่ม `'care_type' => Arr::get($input, 'care_type')` เข้า `NursingProfile::create()`
  (ใน `createNurse()`) และ `$profile->update()` (ใน `updateProfile()`) — `app/Repositories/API/NursingApiRepository.php`
- **ฟอร์มสมัครสมาชิก** (`nursingRegisFrm`, `Authentication.php`) — เพิ่ม select ประเภทผู้ให้บริการ
  (RN/PN/NA/CG/MAID) ต่อจากช่องเพศ เป็น required field ก่อนกด "ถัดไป" ได้ ส่งค่าผ่าน ajax action
  `nursing_register` (`rmn-services.php`)
- **ฟอร์มแก้ไขโปรไฟล์ตัวเอง** (`my-profile.php`, general_info_tab) — เพิ่ม select เดียวกัน ส่งผ่าน
  `nursing_profile_draft_save` (`rmn-services.php`) — ไม่ต้องแก้ `nursing_profile.js` เพราะ draft builder
  อ่าน field ทุกตัวใน form แบบ generic ตาม `name` attribute อยู่แล้ว
- ทดสอบผ่าน local docker แล้ว: สมัครสมาชิกใหม่ตั้ง care_type=RN สำเร็จ, แก้ไขทีหลังเปลี่ยนเป็น PN สำเร็จ
- **พบเพิ่มเติม (ยังไม่แก้ในรอบนี้):** ระหว่างตรวจสอบ flow สมัครพยาบาลเจอบั๊ก 2 จุดที่ใกล้เคียงกับบั๊ก
  NursingHome ที่แก้ไปก่อนหน้า — (1) `NursingController::store()` เรียก `$nursing->createToken()`
  **นอก** `DB::transaction()` ของ `createNurse()` ถ้าออก token ไม่สำเร็จ user+profile จะ commit ค้างไปแล้ว
  ซ้ำรอยเดิมของ NursingHome แค่ย้ายจุดพังไปอีกขั้น (2) รูปถ่ายที่ฟอร์มสมัครบังคับอัปโหลด ถูกทิ้งเงียบๆ ที่
  `rmn_nursing_register()` proxy (ส่งเป็น JSON ไม่มีไฟล์แนบ, backend เองก็ไม่มี logic รับไฟล์ตอนสมัครเลย)
  (3) ช่องรายละเอียดโรคประจำตัว/แพ้ยา (textarea ที่โผล่มาเมื่อเลือก "มี") ไม่ถูกส่งไปเก็บเลย ทั้งไม่มีคอลัมน์
  รองรับด้วย — รอแก้พร้อมกันในรอบถัดไปที่จะทำ Nursing register แบบเต็มรูปแบบ

### Fixed: สมัครสมาชิก NursingHome ค้างถาวรถ้า step 2 (โปรไฟล์) ล้มเหลว

**อาการที่รายงาน:** modal สมัครสมาชิกผู้ให้บริการ (บ้านพักดูแลผู้สูงอายุ) กรอก step 1 (ชื่อ/เบอร์/อีเมล) กด
"ถัดไป" แล้วบันทึกจริงทันที ถ้า step 2 (ที่อยู่/จังหวัด/อำเภอ) กรอกไม่สำเร็จ บัญชีจาก step 1 จะค้างอยู่ใน
`users` ถาวร (unique constraint email/phone) ทำให้ user คนนั้นสมัครใหม่ไม่ได้อีกเลย ไม่มีทาง resume/login

- **Root cause:** `providerRegisFrm` (step 1, ajax action `provider_register`) กับ `providerProfileFrm`
  (step 2, ajax action `provider_profile`) ยิงคนละ request คนละ transaction กันฝั่ง Laravel
  (`NursingHomeController::userCreate()` / `userCreateProfile()`) ไม่มีอะไรเชื่อมกันนอกจาก `user_id` ที่ค้าง
  อยู่ใน DOM ชั่วคราวเท่านั้น
- **แก้โดยรวมเป็น commit เดียวแบบ atomic** — เปลี่ยน `Authentication.php` ให้กด "ถัดไป" แค่สลับหน้าจอ
  client-side เฉยๆ (ไม่ยิง API) แล้วรวม field จากทั้ง 2 step ส่งไปที่ endpoint ใหม่ `NursingHomeController::register()`
  ครั้งเดียวตอนกด "สมัครสมาชิก" ห่อ `NursingHome::create()` + `NursingHomeProfile::create()` +
  ออก Sanctum token ไว้ใน `DB::transaction()` เดียว (`app/Http/Requests/NursingHomeRegisterRequest.php` ใหม่
  รวม validation ของทั้ง 2 step, ตัด `NursingHomeUserCreateRequest`/`NursingHomeProfileCreateRequest` เดิมทิ้ง)
  — retry ปลอดภัย 100% เพราะไม่มีทาง commit บางส่วนได้อีกต่อไป
- **แก้พ่วง:** error message จาก exception ที่ไม่คาดคิดเดิมหลุด SQL ดิบออกไปให้ client (`catch (QueryException)`
  คืน `$e->getMessage()` ตรงๆ) เปลี่ยนเป็น log ไว้ฝั่ง server (`Log::error`) + คืนข้อความสุภาพแทน
- **เพิ่ม default plan** — สมัคร NursingHome สำเร็จแล้วตั้ง `plan = BASIC`, `plan_start` = วันที่สร้างบัญชี
  ทันที (ตาม pattern เดียวกับที่ใช้อยู่แล้วสำหรับ Nursing/Member) — เพิ่ม `plan`/`plan_start` เข้า
  `$fillable` ของ `NursingHome` model ด้วย (เดิมไม่มี เลยถูก mass-assignment กรองทิ้งเงียบๆ ถ้าไม่เพิ่ม)
- ไฟล์ที่แก้: `NursingHomeController.php`, `NursingHomeRegisterRequest.php` (ใหม่), `NursingHome.php`,
  `routes/api.php`, `rmn-services.php`, `Authentication.php` (แก้ทั้ง `frontend/wordpress` และ `deploy`)
- ทดสอบผ่าน local docker แล้ว: กรอกผิดไม่มี row ค้าง, กรอกถูกสร้างสำเร็จแบบ atomic, สมัครซ้ำได้ error สุภาพ
  ไม่ใช่ SQL ดิบ

### Favorites: ปรับหน้า /my-favorites/ เป็น 3 คอลัมน์ + การ์ดแบบข้อมูลติดต่อ + ลบออกจากรายการได้

- **Grid layout** — ทั้งฝั่ง member (ดูรายการที่ตัวเองกด favorite) และฝั่ง provider (ดูว่าใครกด
  favorite ตัวเองบ้าง) ปรับเป็น `grid-cols-1 sm:grid-cols-2 md:grid-cols-3` ตามดีไซน์ที่แนบมา
  (`services/my-favorite/my-favorite.php`)
- **Card style ใหม่** — โชว์ชื่อ, วันที่, เบอร์โทร/อีเมล/Facebook (เท่าที่มีข้อมูลจริง — เดิมไม่มี field
  facebook/lineid เก็บสำหรับ nursing ทุกกรณี), ปุ่มโทรติดต่อ (`services/my-favorite/member.js` เขียน
  `renderCard()` ใหม่ทั้งหมด, `my-favorite.php` ฝั่ง provider view)
- **กดหัวใจเพื่อลบออกจากรายการโปรด** — ทั้ง 2 ทิศทาง:
  - Member ลบ favorite ของตัวเอง — ใช้ endpoint `/favorite/toggle` เดิมที่มีอยู่แล้ว
  - Provider ลบ record ที่ member คนอื่นกด favorite ตัวเองไว้ — endpoint เดิมไม่รองรับ (route เดิมเป็น
    `member.role` เท่านั้น) จึงเพิ่มใหม่:
    - `FavoriteController::removeAsProvider()` + route
      `DELETE {nursing|nursing-home}/provider/favorites/{id}` (ตรวจสิทธิ์ว่า favorite นั้นชี้มาที่
      profile ของ provider คนที่ request จริง ก่อนลบ)
    - ajax proxy `remove_provider_favorite` (`rmn-services.php`)
    - `services/my-favorite/provider-favorite.js` (ไฟล์ใหม่) — ผูก click handler + ลบการ์ดออกจาก DOM
- **เพิ่ม contact info ให้ member เห็นเบอร์โทร/อีเมลของ nursing ที่ favorite ไว้** —
  `FavoriteRepository::paginateByUser()` เพิ่ม eager-load `profile.owner:id,phone,email`
- **Bug ที่เจอระหว่างทาง:** `doShortCode()` เดิม handle เฉพาะ `user_type === 'NURSING'`, ผู้ใช้
  `NURSING_HOME` หลุดไป `noResultView()` เสมอ (หน้าเปล่า) — เพิ่ม case ให้ครบ

### Fixed: ปุ่ม favorite ใช้งานไม่ได้ทั้งเว็บ (ไม่ใช่แค่หน้า my-favorites)

**อาการที่รายงาน:** กดหัวใจ/ปุ่มเปรียบเทียบที่การ์ด nursing แล้วไม่มีอะไรตอบสนองเลย ทั้งที่เคยใช้งานได้

- **Root cause ตัวจริง:** `rmn-services.php` เดิมมี `wp_localize_script('rmn-scripts', 'RMN_AUTH', ...)`
  ขาด key `hasToken` ไป (หลุดหายไปตอนแก้ไฟล์ก่อนหน้าเรื่อง caching/user data leak) ทำให้
  `RMN_AUTH.hasToken` เป็น `undefined` เสมอ ไม่ว่า login อยู่จริงหรือไม่ → ฟังก์ชัน
  `clearCompareIfNoToken()` ใน `rmn-scripts.js` return `true` เสมอ → บล็อกไม่ให้เรียก
  `favoriteProvider()`/`compareNurse()` เลย (event listener ไม่เคยถูกผูกตั้งแต่แรก ไม่ใช่แค่ error
  เงียบๆ) — เติม `hasToken` กลับเข้าไป (ไม่เอา `user` กลับมาด้วย เพราะถูกถอดออกโดยตั้งใจเรื่อง batcache)
- **Bugรอง:** การ์ด nursing บาง render function
  (`services/nursing/nursing-grid-frontend.js`, `services/nursing/nursing-frontend.js`) render ปุ่ม
  favorite ด้วย class `favorite-nurse` แต่ขาด class `add-favorite` ที่ global handler ใน
  `rmn-scripts.js` ดักฟังอยู่ — เทียบกับ `nursinghome-frontend.js` ที่มี class ครบถูกต้อง — เติม
  `add-favorite` กลับเข้าไป

### Favorites: แสดงหัวใจแดงค้างไว้ถ้าเคย favorite แล้ว (ไม่ใช่แค่ตอนกดสดๆ)

- Backend: เพิ่ม `GET /api/favorite/ids?profile_type=X` คืน profile_id ทั้งหมดที่ user นี้เคย favorite
  ไว้ (ไม่จำกัด pagination ต่างจาก endpoint เดิม) — ajax proxy `get_my_favorite_ids`
  (`rmn-services.php`, cache 30s ต่อ token, invalidate ตอน toggle)
- Frontend: ดึง favorited ids มาก่อน render แล้วเติม class `favorited` ให้การ์ดที่ตรงกัน —
  `nursing-grid-frontend.js`, `nursing-frontend.js`, `nursinghome-frontend.js`,
  `nursing-homes-grid-frontend.js` (ไฟล์นี้ไม่เคยมีปุ่ม favorite เลยตั้งแต่ต้น เพิ่มใหม่ทั้งหมด)
- **Bug ที่เจอระหว่างทาง:**
  - `wp_send_json_success(['data' => $ids])` ห่อซ้อน 2 ชั้นโดยไม่ตั้งใจ (เพราะ
    `wp_send_json_success()` ห่อ `data` ให้เองอยู่แล้ว) ทำให้ JS อ่าน `res.data.data` ได้ object แทน
    array → `.map()` throw เงียบๆ → favoritedIds ว่างตลอด แก้เป็น `wp_send_json_success($ids)` ตรงๆ
  - CSS `.favorited img { content: url(...) }` ที่ใช้สลับไอคอนเป็นหัวใจทึบ **ใช้ไม่ได้กับ `<img>` ใน
    Firefox/Safari** (ไม่รองรับ content override บน replaced element) — เปลี่ยนเป็นซ่อนไอคอนเดิม
    (`opacity:0`) แล้ววาดหัวใจทึบด้วย `background-image` แทน (รองรับทุกบราวเซอร์)
  - `NursingHomeHandler.php` enqueue script handle `nursing-grid-frontend` ชนกับ handle เดียวกันที่
    ไฟล์ nursing ใช้ (คนละไฟล์ JS) — WP จะโหลดผิดไฟล์ถ้ามีทั้ง 2 shortcode อยู่หน้าเดียวกัน — เปลี่ยนเป็น
    `nursing-homes-grid-frontend` ให้ unique

### Fixed: icon compare/favorite ไม่ค้างแสดง ต้อง hover ทุกครั้งถึงจะเห็น

**โจทย์:** อยากให้ icon โชว์ค้างไว้ (ไม่ต้อง hover) ถ้าการ์ดนั้นถูกกด favorite หรือกดเข้ารายการเปรียบเทียบ
ไปแล้ว

- เพิ่ม `syncActiveIconStates()` ใน `rmn-scripts.js` — เช็ค localStorage (compare) + class
  `.favorited` แล้วเติม class `.active` ให้ปุ่ม compare ที่เคยกด (เดิมปุ่ม compare ไม่มี state ค้างเลย
  มีแต่ localStorage) เรียกตอนโหลดหน้า + ผูกกับ `MutationObserver` คอยดัก DOM เปลี่ยน (การ์ดโหลดเพิ่ม
  จาก infinite scroll, compare modal re-render) sync ซ้ำอัตโนมัติ
- **Bug รอบแรก:** ใช้ `.action-icons:has(.favorited)` โชว์ทั้งกลุ่ม icon พร้อมกัน — ผลคือถ้า favorite
  active อยู่ ปุ่ม compare ข้างๆ (ที่ไม่เคยกด) โผล่มาด้วยทั้งที่ไม่ควร แก้เป็นควบคุม `visibility`
  รายไอคอนแทนที่ระดับ container, scope เฉพาะ `.nurse-item`/`.nurse-home-item` (กันไม่ให้กระทบหน้า
  โปรไฟล์เดี่ยวที่ใช้ class เดียวกันแต่ควรโชว์ตลอดอยู่แล้ว)
- **Bug รอบสอง:** rule ที่ทำให้ icon active มองเห็นได้ (`.action-icon.active`) specificity ต่ำกว่า rule
  ที่ซ่อนโดย default (`.nurse-item .action-icons .action-icon`, 3 class ผูกกัน) — แพ้ทุกครั้งไม่ว่าจะ
  มาทีหลังหรือมี `!important` ก็ตาม แก้โดยเพิ่ม specificity ของ override rule ให้เท่ากันขึ้นไป
  (`tailwind.css`)

### Fixed: bug เล็กๆ ระหว่างทาง

- `Uncaught ReferenceError: axios is not defined` เกิด 2 จุดจากคนละสาเหตุ:
  - `Authentication.php` — inline `<script>` เรียก `axios.get(...)` ทันทีตอน parse หน้า (ก่อน
    footer script ของ axios โหลดเสร็จ เพราะ axios enqueue แบบ `in_footer`) ห่อด้วย
    `window.addEventListener('load', ...)` แทน
  - `member.js`/`provider-favorite.js` — เพิ่ม `whenAxiosReady()` guard (poll
    `typeof axios !== "undefined"`) กันกรณีปลั๊กอิน cache/optimize บนเว็บจริง delay สคริปต์ CDN
    ภายนอกจนกว่าจะมี user interaction
- `member.js` แสดงรูปโปรไฟล์ไม่ออก — ใช้ key `profile.coverImage` (camelCase) ผิด ที่จริง API คืนมาเป็น
  `profile.cover_image` (snake_case) — ยืนยันจาก response จริงก่อนแก้ (ไฟล์อื่นที่มีอยู่แล้วใช้
  `cover_image` ถูกต้องอยู่แล้ว เป็น bug เฉพาะไฟล์นี้ที่เขียนใหม่)
- ปรับ UI เล็กน้อยตามที่ขอ: `#favorite_grid` min-height 465px → 318px, ชื่อในการ์ด (`<h3>`) เพิ่ม
  `text-[18px]`

### แก้ลิงก์เสียในเมนูโปรไฟล์มือถือ (`Authentication.php`)

- "การสมัครสมาชิก", "แก้ไขข้อมูลส่วนตัว" (2 จุด) เดิมเป็น `href="http://"`/`href="#"` (placeholder ค้าง)
  — แก้ให้ชี้ไปหน้าเป้าหมายที่ถูกต้อง (`/subscription`, `/my-account`)
- ช่อง "การนัดหมาย" ใน grid 2 คอลัมน์ — สำหรับ role MEMBER เปลี่ยนข้อความเป็น "ประวัติการติดต่อ" +
  ลิงก์ไป `/my-contacts/`, role provider คงเดิมไว้ — **ทำผ่าน JS หลังดึง user สดๆ ผ่าน
  `get_current_user` ajax เท่านั้น ไม่ branch ด้วย PHP ตรงๆ** เพราะฟังก์ชัน `mb_navigation()` มี
  comment เตือนชัดเจนว่าหน้านี้ถูก cache ข้าม visitor ได้ ห้าม render ตามสถานะ login/role ฝั่ง
  server เด็ดขาด

### Deploy infrastructure (เริ่มทำ, ยังไม่เสร็จ)

- ตั้ง SSH key เฉพาะสำหรับ deploy pipeline เข้า DigitalOcean droplet ที่รัน backend
  (`services.ratemynurse.org`, docker compose: `rmn_laravel_backend` + nginx + certbot)
- เขียน `.github/workflows/deploy-backend.yml` — auto deploy เฉพาะ `backend/laravel/**` เมื่อ push
  เข้า `main` (rsync ไฟล์ขึ้น server ยกเว้น `.env`/`vendor`/`storage`/`bootstrap/cache` แล้วรัน
  `composer install` + clear cache + `migrate --force` ใน container)
- **ยังไม่เสร็จ:** ต้องเพิ่ม GitHub repo secrets (`DO_HOST`, `DO_USER`, `DO_SSH_PRIVATE_KEY`) ก่อน
  pipeline จะรันได้จริง — ระหว่างรอ sync ไฟล์ backend ที่แก้ไปแล้ว (`FavoriteController.php`,
  `routes/api.php`, `FavoriteRepository.php`) ขึ้น production ตรงๆ ผ่าน SSH ที่ตั้งไว้ เพื่อไม่ให้
  งานเร่งด่วนติดค้าง
- **ข้อควรระวังที่ยังไม่แก้:** SSH key ที่ใช้ตอนนี้คือ root เต็มรูปแบบ ยังไม่ได้ทำ non-root deploy
  user หรือ forced-command restriction — ผู้ใช้รับทราบความเสี่ยงแล้วขอทำแบบเดิมไปก่อนเพราะงานเร่ง
  ด่วน ควรกลับมาทำให้ปลอดภัยขึ้นทีหลัง

## 2026-07-15

### Local dev environment (Docker)

- เปิดใช้ service `wordpress` + `rmn_wp_db` ใน `docker-compose.yml` (เดิม comment ไว้)
- เพิ่ม service `proxy` (Caddy) จำลอง production domains (`ratemynurse.org`, `www.ratemynurse.org`,
  `services.ratemynurse.org`) ด้วย HTTPS จริง (self-signed local CA) — proxy ไปยัง `wordpress:80` /
  `laravel:80` ตามโดเมน ดู `caddy/Caddyfile`
- เพิ่ม network alias ให้ service `proxy` บน network `internal` เพื่อให้ container อื่น (เช่น
  `wordpress`) resolve โดเมนจำลองเหล่านี้ได้เอง (แก้ปัญหา `wp_remote_post`/`wp_remote_get` ฝั่ง server
  เคยหลุดไปหา production จริงเพราะไม่มี DNS override ในเครือข่าย Docker)
- เพิ่ม mu-plugin `frontend/wordpress/wp-content/mu-plugins/local-dev-overrides.php` (เฉพาะ local,
  ไม่ sync ขึ้น production) ปิด SSL verify เฉพาะตอนเรียก host จำลองข้างต้น เพราะใช้ self-signed cert
- Import ฐานข้อมูลจริงจาก production เข้า local:
  - Laravel (`laravel_db`) จากไฟล์ dump แบบ full (structure + data) — sync migrations table ให้ตรงกับ
    migration files ที่มีในเครื่อง (มี migration drift: production มี column เพิ่มในหลายตารางที่ migration
    ไฟล์ในเครื่องไม่มี — แก้ด้วยการ import structure จริงจาก production แทนการเดา schema)
  - WordPress (`wp_db`) จากไฟล์ export phpMyAdmin (data-only, ไม่มี DROP TABLE) — ต้อง drop/recreate
    database ก่อน import เพราะ volume เดิมมีข้อมูลทดสอบเก่าค้างอยู่
- สิ่งที่ยังขาดสำหรับ parity เต็มรูปแบบ (ไม่ blocking): theme `retrospect` และปลั๊กอินอีกหลายตัว
  (jetpack, gutenberg, oxygen, page-optimize, crowdsignal-forms ฯลฯ) ที่ production active ใช้งานอยู่
  แต่ไม่มีไฟล์ในเครื่อง local

### Fixed: Login / OTP flow

**อาการที่รายงาน:** กด login กรอกเบอร์แล้วเด้งกลับให้กรอกใหม่ 2-3 รอบ, และเครื่องทดสอบที่ไม่เคย login
กลับเห็นข้อมูลบัญชีของคนอื่นที่เคย login ไปก่อนหน้า

- **ป้องกันกดซ้ำระหว่างรอ OTP** (`services/authentication/Authentication.php`) — เพิ่ม loading overlay
  เต็มจอ + flag กันเรียกซ้ำระหว่างรอ server ตอบกลับทั้งตอนขอ OTP และยืนยัน OTP
- **OTP หมดอายุเร็วเกินไป** (`backend/laravel/app/Http/Controllers/API/OtpController.php`) — ขยายจาก
  30 วินาที เป็น 60 วินาที (30 วินาทีสั้นเกินไปแม้แต่ automated test ยังพลาดเวลา)
- **Root cause ข้อมูล user เห็นข้ามคน:** เว็บ host อยู่บน WordPress.com ซึ่งใช้ cache ของตัวเอง
  (Batcache) ที่เช็คสถานะ "login แล้วหรือยัง" จาก cookie มาตรฐานของ WordPress เท่านั้น
  (`wordpress_logged_in_*`) แต่ระบบนี้ใช้ cookie ที่ตั้งชื่อเอง (`access_token`, `is_auth`) ซึ่ง Batcache
  ไม่รู้จัก จึงมองผู้ใช้ที่ login ผ่านระบบนี้เป็น "ผู้เข้าชมทั่วไป" และอาจเสิร์ฟหน้า HTML เก่าที่ cache ไว้
  (ซึ่งมีข้อมูลส่วนตัวของคนก่อนหน้าฝังอยู่) ให้คนถัดไปที่เปิดหน้าเดียวกัน
- แก้โดยย้ายข้อมูลส่วนตัวของผู้ใช้ทั้งหมดออกจาก HTML ที่ cache ได้ ไปดึงผ่าน ajax action
  `get_current_user` (ที่มีอยู่แล้วในระบบ, `admin-ajax.php` ไม่ถูก cache) แทนเสมอ — pattern เดียวกับที่
  WooCommerce ใช้กับ cart fragments:
  - `rmn-services.php` — เอา `'user' => rmn_get_cached_user_data()` ออกจาก `RMN_AUTH` ที่ฝังใน HTML
    (ลบฟังก์ชัน `rmn_get_cached_user_data()` ที่ไม่ใช้แล้วทิ้งด้วย)
  - `Authentication.php` (`updateUserUI()`, เมนูเดสก์ท็อป) — เลิกเชื่อ `RMN_AUTH.user`/`hasToken` ที่ฝัง
    มา ดึงผ่าน ajax เสมอ
  - `Authentication.php` (`mb_navigation()`, เมนูมือถือ) — เดิม render ชื่อ user จริงและเลือก
    login/account tab จาก `$guard->isLogged()`/`$guard->me()` ฝั่ง server ตรงๆ (ฝังลง HTML ที่ cache
    ได้) แก้เป็น render ทั้ง 2 สถานะเสมอ (default โชว์ "เข้าสู่ระบบ") แล้วให้ JS สลับ/เติมชื่อจริงผ่าน ajax
    หลังโหลดหน้าเสร็จ
  - `job-post.php` — แก้จุดเดียวกัน (ดึง user ผ่าน ajax เสมอแทนใช้ค่าที่ฝังมา)
- เพิ่ม `Cache-Control: private, no-store` + `Vary: Cookie` ให้ทุก response ที่มี cookie
  `access_token` (`rmn-services.php`, hook `send_headers`) เพื่อกันไม่ให้ cache layer ใดๆ เก็บหน้าที่
  personalized เข้า cache ตั้งแต่แรก
- **ยังไม่ได้แก้ (พบเพิ่มเติม, ความรุนแรงต่ำกว่า):** `mb_navigation()` ยังใช้
  `$guard->getProfileType()` เลือก icon เมนู (favorites/search สำหรับ member vs contacts/overview
  สำหรับ provider) ฝั่ง server เหมือนกัน เป็นบั๊กกลุ่มเดียวกันแต่ไม่หลุดข้อมูลตัวตนคน แค่โชว์ icon ผิดชุด

### ยืนยันผลแล้ว (local)

- ทดสอบ OTP request → verify → reload ผ่าน WordPress admin-ajax จริง (ไม่ใช่ยิง Laravel ตรง) สำเร็จ
- ยืนยันว่า homepage HTML หลัง login ไม่มีข้อมูลผู้ใช้ฝังอยู่แล้ว (`RMN_AUTH` เหลือแค่ `ajaxUrl`)
- ยืนยันว่า `get_current_user` ajax ยังคืนข้อมูล user ถูกต้องครบเมื่อมี cookie session ที่ถูกต้อง
- **ยังทดสอบ "ข้ามคนไม่เห็นข้อมูลกัน" บน production จริงไม่ได้** เพราะ local ไม่มี WordPress.com
  Batcache ให้จำลอง ต้อง deploy แล้วเทสสลับ 2 บัญชีจริงบน production เท่านั้น

### พร้อม deploy

ไฟล์ที่แก้ไขทั้งหมดถูกคัดลอกไว้ใน `deploy/` (จัด path ให้ตรงกับปลายทางจริงแล้ว) พร้อมรายละเอียดและ
checklist ทดสอบใน `deploy/FILES_TO_UPLOAD.txt`:

- `wp-content/plugins/rmn-services/rmn-services.php`
- `wp-content/plugins/rmn-services/services/authentication/Authentication.php`
- `wp-content/plugins/rmn-services/services/board/job-post.php`
- `app/Http/Controllers/API/OtpController.php` (deploy แยกไปที่ Laravel VPS)
