<p align="center">
  <img src="images/logo-kampus.png" alt="Logo Kampus" width="128"/>
</p>

# UHN Terkini

Aplikasi **UHN Terkini** merupakan platform berbasis web yang dibuat untuk menyediakan informasi terkini seputar Universitas Hindu Negeri. Proyek ini dikembangkan menggunakan PHP sebagai backend utama, CSS untuk styling, dan JavaScript untuk interaktivitas.

## Fitur Utama

- Sistem manajemen berita kampus
- Publikasi pengumuman terbaru
- Pencarian berita dan filter kategori
- Responsive design untuk akses perangkat mobile dan desktop
- Pendaftaran pengguna dan autentikasi sederhana

## Teknologi yang Digunakan

<table>
  <tr>
    <td align="center">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="48" height="48" alt="PHP Logo"/>
      <br/><b>PHP</b><br/>82%
    </td>
    <td align="center">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="48" height="48" alt="CSS3 Logo"/>
      <br/><b>CSS</b><br/>15.5%
    </td>
    <td align="center">
      <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="48" height="48" alt="JavaScript Logo"/>
      <br/><b>JavaScript</b><br/>2.5%
    </td>
  </tr>
</table>

## Instalasi (Menggunakan Laragon)

1. **Pastikan Laragon sudah terpasang di komputer Anda.**  
   Unduh di [https://laragon.org/download/](https://laragon.org/download/)
2. **Klon repositori ini ke folder `www` milik Laragon:**
   ```bash
   git clone https://github.com/Ananda993/uhn_terkini.git
   ```
3. **Masuk ke direktori `uhn_terkini` di dalam folder `www`:**
   ```
   C:\laragon\www\uhn_terkini
   ```
4. **Jika aplikasi memerlukan database:**
   - Buka Laragon dan jalankan Apache & MySQL.
   - Buat database baru menggunakan phpMyAdmin atau HeidiSQL (misal: `uhn_terkini`).
   - Import file SQL yang disediakan dalam repositori (jika ada).
5. **Konfigurasi koneksi database** di file konfigurasi (misal: `config.php`) sesuai dengan pengaturan database Laragon Anda.
6. **Akses aplikasi melalui browser:**
   ```
   http://uhn_terkini.test
   ```
   (Pastikan nama folder sama dengan nama domain lokal Anda, atau cek menu [Menu > www > Quick app > Hostname] di Laragon.)

## Kontribusi

Kontribusi terbuka untuk pengembangan dan penambahan fitur baru. Silakan lakukan fork, buat branch baru, dan ajukan pull request.

## Anggota Pengembang

- I Putu Agus Ananda Guna Prasetya
- gede Adi Pramana
- I Made Hanggara Kesuma Dewa


## Kontak & Informasi

Untuk pertanyaan lebih lanjut, silakan hubungi salah satu anggota tim melalui GitHub atau email yang tertera pada profil masing-masing.

---

Terima kasih sudah menggunakan UHN Terkini!
