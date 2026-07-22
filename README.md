<div align="center">

<img src="public/favicon.svg" width="72" height="72" alt="Demokrat" />

# Demokrat

**Siyasi parti üyeleri için açık kaynak talep & oylama platformu**

Üyeler serbest metin _talep_ oluşturur, diğer üyeler yukarı/aşağı oy vererek talepleri öne çıkarır.
Gerçek zamanlı akış, rol tabanlı yönetim ve uçtan uca denetim günlüğü ile birlikte gelir.

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Vue](https://img.shields.io/badge/Vue-3-4FC08D?logo=vuedotjs&logoColor=white)
![Inertia](https://img.shields.io/badge/Inertia.js-3-9553E9)
![License](https://img.shields.io/badge/license-MIT-green)

</div>

---

## İçindekiler

- [Özellikler](#özellikler)
- [Teknoloji Yığını](#teknoloji-yığını)
- [Gereksinimler](#gereksinimler)
- [Kurulum](#kurulum)
- [Çalıştırma](#çalıştırma)
- [Roller ve Yetkiler](#roller-ve-yetkiler)
- [Mimari](#mimari)
- [Gerçek Zamanlı Akış (Reverb)](#gerçek-zamanlı-akış-reverb)
- [Test](#test)
- [Kod Kalitesi](#kod-kalitesi)
- [Katkı](#katkı)
- [Lisans](#lisans)

---

## Özellikler

- 📝 **Talep oluşturma** — üyeler serbest metin talep paylaşır.
- ⬆️⬇️ **Toggle oylama** — aynı yöne tekrar basınca oy silinir, ters yöne basınca değişir. Kullanıcı başına talep başına tek oy (veritabanı seviyesinde `unique`).
- 🔒 **Silme kilidi** — bir talep oy aldıktan sonra sahibi tarafından silinemez/düzenlenemez; moderatör ve üzeri her zaman yönetebilir.
- 🧭 **Ana sayfa akışı** — Twitter benzeri sonsuz kaydırma; **rastgele**, **en çok oylanan**, **en çok desteklenen**, **en az desteklenen** sıralama modları. Rastgele mod, oturum başına sabit tohum (seed) ile tekrarsız/atlamasız kaydırma sağlar.
- ⚡ **Gerçek zamanlı** — yeni talepler ve oy sayacı değişimleri Laravel Reverb (WebSocket) ile bağlı istemcilere anlık yansır.
- 👥 **Çoklu rol** — `superadmin`, `admin`, `moderator`, `member`; bir kullanıcı aynı anda birden çok role sahip olabilir ([spatie/laravel-permission](https://spatie.be/docs/laravel-permission)).
- 📜 **Detaylı denetim günlüğü** — talep/oy CRUD, giriş/çıkış/başarısız giriş/kayıt ve rol atama/kaldırma işlemleri loglanır ([spatie/laravel-activitylog](https://spatie.be/docs/laravel-activitylog)).
- 🛡️ **Yönetim paneli** — rol yönetimi ve aktivite günlüğü görüntüleyici (yalnızca admin/superadmin).
- 🔑 **Hazır kimlik doğrulama** — kayıt, giriş, e-posta doğrulama, iki faktör ve passkey (Laravel Fortify).

---

## Teknoloji Yığını

| Katman | Teknoloji |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Vue 3 + TypeScript, Inertia.js 3 |
| Stil | Tailwind CSS 4, shadcn-vue / reka-ui |
| Kimlik doğrulama | Laravel Fortify (passkey + 2FA) |
| Roller/İzinler | spatie/laravel-permission |
| Denetim günlüğü | spatie/laravel-activitylog |
| Gerçek zaman | Laravel Reverb + Laravel Echo |
| Test | PHPUnit 12 |
| Varsayılan DB | SQLite (MySQL/PostgreSQL uyumlu) |

---

## Gereksinimler

- **PHP** 8.3+ (SQLite eklentisi etkin)
- **Composer** 2.x
- **Node.js** 20+ ve npm
- (Opsiyonel) MySQL/PostgreSQL — üretim için

---

## Kurulum

```bash
# 1. Depoyu klonla
git clone https://github.com/sencerhan/demokrat.git
cd demokrat

# 2. PHP bağımlılıkları
composer install

# 3. Ortam dosyası ve uygulama anahtarı
cp .env.example .env
php artisan key:generate

# 4. Reverb (WebSocket) anahtarlarını üret
php artisan reverb:install

# 5. SQLite veritabanı dosyası
touch database/database.sqlite

# 6. Migrasyonlar + roller ve örnek süper yönetici
php artisan migrate --seed

# 7. Frontend bağımlılıkları
npm install
```

> **Not:** `migrate --seed`, dört rolü oluşturur ve `test@example.com` (şifre: `password`) hesabını **superadmin** olarak atar. Üretimde bu hesabın şifresini değiştirin veya kaldırın.

`.env` içindeki ilgili ayarlar:

```dotenv
APP_NAME=Demokrat
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http
```

---

## Çalıştırma

### Geliştirme (tek komut)

```bash
composer dev
```

Bu komut aynı anda çalıştırır: uygulama sunucusu, kuyruk dinleyici (`queue:listen`), log kuyruğu (`pail`) ve Vite geliştirme sunucusu.

### Gerçek zaman için Reverb (ayrı terminal)

```bash
php artisan reverb:start
```

> Gerçek zamanlı oy/talep güncellemelerinin çalışması için Reverb sunucusu ayrıca çalışıyor olmalıdır. Üretimde `reverb:start` ve `queue:work` süreçlerini bir süreç yöneticisi (Supervisor / systemd) ile denetleyin.

### Manuel çalıştırma

```bash
php artisan serve        # uygulama
php artisan queue:work   # kuyruk (broadcast işleri)
php artisan reverb:start # websocket
npm run dev              # veya: npm run build
```

Uygulama varsayılan olarak `http://localhost:8000` adresinde açılır. Ana sayfa (`/`) herkese açık talep akışıdır; oy vermek veya talep oluşturmak için giriş gerekir.

---

## Roller ve Yetkiler

| Rol | Yetkiler |
| --- | --- |
| **member** (üye) | Talep oluşturur, oy verir; kendi talebini **yalnızca oy gelmeden önce** düzenleyebilir/silebilir. |
| **moderator** | Tüm taleplerde: düzenleme, durum değiştirme, silme. Rol yönetimine ve aktivite günlüğüne erişemez. |
| **admin** | Moderatör yetkileri + **moderator/member** rollerini atama/kaldırma + aktivite günlüğü. `admin`/`superadmin` rolü veremez. |
| **superadmin** | Tüm roller dahil sınırsız rol yönetimi. Son superadmin kendi rolünü kaldıramaz (kilitlenme koruması). |

Talep durumları: `beklemede` · `incelendi` · `kabul edildi` · `reddedildi`.

---

## Mimari

Proje, iki değişmez kural etrafında kurulur (bkz. [`CLAUDE.md`](CLAUDE.md)):

1. **Servis katmanı zorunlu** — tüm iş mantığı `app/Services/*Service.php` içindedir; controller'lar yalnızca HTTP akışını yönetir.
2. **Test zorunlu** — her iş birimi için PHPUnit testi yazılır.

```
app/
├── Enums/            TalepStatus, VoteDirection, RoleName, TalepSortMode
├── Events/           TalepCreated, TalepVoteCast, TalepStatusUpdated (broadcast)
├── Http/
│   ├── Controllers/  TalepController, TalepVoteController, Admin\*
│   ├── Requests/      FormRequest tabanlı doğrulama + yetki
│   └── Resources/     TalepResource (per-viewer `can` blokları)
├── Listeners/        LogAuthenticationActivity (auth denetim günlüğü)
├── Models/           Talep, TalepVote, User
├── Policies/         TalepPolicy, UserPolicy
└── Services/         TalepService, VoteService, TalepFeedService, RoleService
```

Öne çıkan tasarım kararları:

- **Yarış-güvenli oylama** — `VoteService::castVote` transaction içinde `lockForUpdate` ile talep satırını kilitler; denormalize sayaçlar (`upvotes_count`, `downvotes_count`, `score`, `total_votes_count`) tutarlı güncellenir.
- **Kararlı rastgele sıralama** — her talebe oluşturmada kalıcı bir `random_key` atanır; rastgele mod bu anahtarı tohumdan başlayan bir rotasyonla sıralar, böylece sonsuz kaydırmada tekrar/atlama olmaz.
- **Broadcast yalnızca commit sonrası** — event'ler transaction dışında tetiklenir; geri alınan yazımda yayın yapılmaz.

---

## Gerçek Zamanlı Akış (Reverb)

Genel (public) `talepler` kanalına yayınlanan olaylar:

| Olay | Yayın adı | İçerik |
| --- | --- | --- |
| `TalepCreated` | `talep.created` | yeni talep verisi |
| `TalepVoteCast` | `talep.vote-cast` | talep id + güncel sayaçlar |
| `TalepStatusUpdated` | `talep.status-updated` | talep id + yeni durum |

Frontend bu olayları `@laravel/echo-vue`'nun `useEchoPublic` composable'ı ile dinler ve akışı canlı günceller.

---

## Test

```bash
php artisan test          # tüm suite (95 test)
php artisan test --filter VoteServiceTest
```

Kapsam: servis birim testleri (oy toggle/switch aritmetiği, rastgele sayfalama tekrarsızlığı, rol servisi), feature testleri (talep CRUD + yetki, oylama, akış sıralaması, rol atama matrisi, denetim günlüğü kayıtları) ve `Event::fake` ile broadcast doğrulaması.

Test ortamı SQLite bellek-içi veritabanı kullanır; Reverb'e bağlanmaz (`BROADCAST_CONNECTION=null`).

---

## Kod Kalitesi

```bash
composer test          # pint (stil) + phpstan (statik analiz) + artisan test
vendor/bin/pint        # PHP kod stili düzeltme
vendor/bin/phpstan     # statik analiz (larastan)

npm run lint           # ESLint --fix
npm run format         # Prettier
npm run types:check    # vue-tsc
```

---

## Katkı

1. Bir dal (branch) açın: `git checkout -b ozellik/yeni-sey`
2. Değişikliğinizi yapın — **servis katmanı + test** kurallarına uyun.
3. `composer test` ve `npm run lint:check && npm run types:check` komutlarının yeşil olduğundan emin olun.
4. Pull request açın.

---

## Lisans

[MIT](LICENSE) lisansı ile açık kaynak olarak yayınlanmıştır.
