# Proje Talimatları

Bu proje bir Laravel + Inertia (Vue/TypeScript) uygulamasıdır. Aşağıdaki iki kural
**her talimatta istisnasız** uygulanır; bir görev bu iki maddeyi karşılamadan
tamamlanmış sayılmaz.

## 1. Servis Katmanı Zorunluluğu

- Tüm iş mantığı (business logic) `app/Services` altındaki Service sınıflarında yazılır.
  Klasör yoksa oluşturulur.
- Controller'lar (`app/Http/Controllers`) yalnızca HTTP request/response akışını
  yönetir: request'i doğrular (`app/Http/Requests`), ilgili Service'i çağırır,
  sonucu Inertia response'una çevirir. Controller içine doğrudan sorgu/iş kuralı
  yazılmaz.
- Her Service, ait olduğu domain ile isimlendirilir: `App\Services\{Domain}Service`
  (örn. `UserService`, `PostService`).
- Service'ler Controller'a constructor injection ile enjekte edilir; Laravel'in
  service container'ı üzerinden çözülür, `new` ile elle oluşturulmaz.
- Fortify tarzı tekil, tek-seferlik işlemler için `app/Actions` kullanılabilir,
  ancak birden fazla yerden çağrılan veya domain kuralı içeren mantık Service'e
  taşınır.
- Model'lere doğrudan Controller'dan Eloquent sorgusu yazılmaz; sorgular Service
  (gerekirse Repository/QueryBuilder yardımcı sınıfı) üzerinden yapılır.

## 2. Test Zorunluluğu

- Her talimatta (yeni özellik, bugfix, refactor, servis katmanına taşıma) ilgili
  test **mutlaka** yazılır veya güncellenir. Test yazılmadan görev tamamlanmış
  sayılmaz.
- Testler mevcut projedeki stille birebir uyumlu olur: PHPUnit class tabanlı
  (`Tests\TestCase`'den extend eden), `tests/Feature` ve `tests/Unit` altında,
  metod isimleri `test_...` biçiminde (bkz. `tests/Feature/ExampleTest.php`).
- Yeni/değişen bir Service için `tests/Unit` altında, bağımlılıkları mock'lanmış
  bir Unit test yazılır.
- Yeni/değişen bir endpoint için `tests/Feature` altında, `RefreshDatabase`
  trait'i kullanan bir Feature test yazılır (HTTP response + veritabanı durumu
  doğrulanır).
- Değişiklik tamamlanmadan önce `php artisan test` (veya `vendor/bin/phpunit`)
  çalıştırılıp ilgili testlerin (ve mümkünse tüm suite'in) yeşil olduğu doğrulanır;
  sonuç kullanıcıya raporlanır.

## Görev Akışı

Her talimat şu sırayla uygulanır:

1. İlgili Service sınıfı/metodu `app/Services` altında yazılır veya güncellenir.
2. Controller, iş mantığını doğrudan içermeyecek şekilde Service'i çağıracak
   şekilde düzenlenir.
3. Service ve/veya endpoint için test yazılır (madde 2).
4. Testler çalıştırılır ve sonuç doğrulanır.
