<?php
$adminTitle = 'Upload Berita';
include __DIR__ . '/includes/header.php';

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'berita';
}

function sanitizeRichText($html) {
    $allowed = '<p><br><b><strong><i><em><u><s><h1><h2><h3><h4><ul><ol><li><blockquote><a><span><div><img><table><thead><tbody><tr><th><td><pre><code><hr><mark>';
    $clean = strip_tags($html, $allowed);
    $clean = preg_replace('/\son\w+="[^"]*"/i', '', $clean);
    $clean = preg_replace('/javascript:/i', '', $clean);
    $clean = preg_replace('/expression\([^)]*\)/i', '', $clean);
    return $clean;
}

$errors = [];
$judul = '';
$sumber = '';
$deadline = '';
$categoryId = '';
$deskripsi = '';
$status = 'approved';
$editorPlaceholder = 'Tulis isi berita di sini...';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $sumber = trim($_POST['sumber'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $deskripsi = $_POST['deskripsi'] ?? '';
    $status = in_array($_POST['status'] ?? 'approved', ['approved', 'pending'], true) ? $_POST['status'] : 'approved';

    $plainText = trim(strip_tags($deskripsi));
    if ($plainText === $editorPlaceholder) {
        $deskripsi = '';
        $plainText = '';
    }

    if ($judul === '') { $errors[] = 'Judul wajib diisi.'; }
    if ($sumber === '') { $errors[] = 'Sumber penerbit wajib diisi.'; }
    if ($categoryId <= 0) { $errors[] = 'Kategori wajib dipilih.'; }
    if ($plainText === '') { $errors[] = 'Isi berita wajib diisi.'; }

    $gambarName = null;
    if (!empty($_FILES['gambar']['name'])) {
        $uploadDir = __DIR__ . '/../foto/';
        $tmpName = $_FILES['gambar']['tmp_name'];
        $fileInfo = @getimagesize($tmpName);
        if ($fileInfo === false) {
            $errors[] = 'File gambar tidak valid.';
        } else {
            if ($_FILES['gambar']['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Ukuran gambar maksimal 2MB.';
            }
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowedExt, true)) {
                $errors[] = 'Format gambar harus JPG, PNG, atau WEBP.';
            } else {
                $gambarName = 'info_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($tmpName, $uploadDir . $gambarName)) {
                    $errors[] = 'Gagal mengunggah gambar.';
                }
            }
        }
    }

    if (empty($errors)) {
        $slugBase = slugify($judul);
        $slug = $slugBase;
        $i = 1;
        $checkStmt = $conn->prepare('SELECT COUNT(*) as c FROM informasi WHERE slug = ?');
        while (true) {
            $checkStmt->bind_param('s', $slug);
            $checkStmt->execute();
            $exists = $checkStmt->get_result()->fetch_assoc()['c'] > 0;
            if (!$exists) {
                break;
            }
            $i++;
            $slug = $slugBase . '-' . $i;
        }
        $checkStmt->close();

        $cleanHtml = sanitizeRichText($deskripsi);
        $stmt = $conn->prepare('INSERT INTO informasi (judul, slug, sumber, deskripsi, gambar, deadline, category_id, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $userId = (int)($_SESSION['user_id'] ?? 1);
        $stmt->bind_param('ssssssiis', $judul, $slug, $sumber, $cleanHtml, $gambarName, $deadline, $categoryId, $userId, $status);
        $stmt->execute();
        $stmt->close();

        header('Location: /admin/berita-upload.php?msg=created');
        exit;
    }
}

$categories = $conn->query('SELECT id, nama FROM categories ORDER BY nama ASC');
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Upload Berita</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;">Buat berita baru sebagai admin</p>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
            <div class="auth-alert auth-alert-success" style="max-width:420px; margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i> Berita berhasil dibuat.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="auth-alert auth-alert-error" style="max-width:420px; margin-bottom:1rem;">
                <?php foreach ($errors as $err): ?>
                    <div><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="admin-card admin-form-card">
            <div class="admin-card-header">
                <div>
                    <div style="font-size:1rem; font-weight:700; color:#0f172a;">Form Upload Berita</div>
                    <div class="admin-help">Lengkapi data berikut untuk menerbitkan berita baru.</div>
                </div>
                <div class="admin-help">Wajib isi: Judul, Kategori, Isi Berita</div>
            </div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data" class="admin-form" id="beritaForm">
                    <div class="admin-field">
                        <label class="admin-label" for="judul">Judul Berita</label>
                        <input type="text" id="judul" name="judul" value="<?= htmlspecialchars($judul) ?>" class="auth-input" placeholder="Masukkan judul" required>
                    </div>

                    <div class="admin-field">
                        <label class="admin-label" for="sumber">Sumber Penerbit</label>
                        <input type="text" id="sumber" name="sumber" value="<?= htmlspecialchars($sumber) ?>" class="auth-input" placeholder="Contoh: FDA, FDD" required>
                        <div class="admin-help">Tuliskan sumber/instansi penerbit berita.</div>
                    </div>

                    <div class="admin-grid-2">
                        <div class="admin-field">
                            <label class="admin-label" for="category_id">Kategori</label>
                            <select id="category_id" name="category_id" class="auth-select" required>
                                <option value="">Pilih kategori</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nama']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="deadline">Deadline (Opsional)</label>
                            <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars($deadline) ?>" class="auth-input">
                            <div class="admin-help">Kosongkan jika tidak ada batas waktu.</div>
                        </div>
                    </div>

                    <div class="admin-grid-2">
                        <div class="admin-field">
                            <label class="admin-label" for="status">Status</label>
                            <select id="status" name="status" class="auth-select">
                                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                            <div class="admin-help">Pending akan menunggu persetujuan.</div>
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="gambarInput">Gambar (Opsional)</label>
                            <div class="upload-field">
                                <div class="upload-preview" id="gambarPreviewBox">
                                    <span id="gambarPreviewText">No preview</span>
                                    <img id="gambarPreview" alt="Preview gambar">
                                </div>
                                <div class="upload-meta">
                                    <div class="file-name" id="gambarFileName">Belum ada file</div>
                                    <div class="file-hint">JPG, PNG, atau WEBP. Maks 2MB.</div>
                                    <input type="file" id="gambarInput" name="gambar" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label class="admin-label" for="tiptapEditor">Isi Berita</label>
                        <div class="editor-shell">
                            <div class="tiptap-toolbar" id="tiptapToolbar">
                                <div class="tiptap-group">
                                    <button type="button" class="editor-btn" data-action="bold" title="Bold"><i class="fas fa-bold"></i></button>
                                    <button type="button" class="editor-btn" data-action="italic" title="Italic"><i class="fas fa-italic"></i></button>
                                    <button type="button" class="editor-btn" data-action="underline" title="Underline"><i class="fas fa-underline"></i></button>
                                    <button type="button" class="editor-btn" data-action="strike" title="Strike"><i class="fas fa-strikethrough"></i></button>
                                    <button type="button" class="editor-btn" data-action="codeBlock" title="Code Block"><i class="fas fa-code"></i></button>
                                </div>
                                <div class="tiptap-group">
                                    <button type="button" class="editor-btn" data-action="bulletList" title="Bullet list"><i class="fas fa-list-ul"></i></button>
                                    <button type="button" class="editor-btn" data-action="orderedList" title="Numbered list"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" class="editor-btn" data-action="blockquote" title="Quote"><i class="fas fa-quote-right"></i></button>
                                </div>
                                <div class="tiptap-group">
                                    <select id="headingSelect" class="auth-select editor-select" data-action="heading">
                                        <option value="p">Paragraph</option>
                                        <option value="h2">Heading 2</option>
                                        <option value="h3">Heading 3</option>
                                        <option value="h4">Heading 4</option>
                                    </select>
                                    <button type="button" class="editor-btn" data-action="alignLeft" title="Align Left"><i class="fas fa-align-left"></i></button>
                                    <button type="button" class="editor-btn" data-action="alignCenter" title="Align Center"><i class="fas fa-align-center"></i></button>
                                    <button type="button" class="editor-btn" data-action="alignRight" title="Align Right"><i class="fas fa-align-right"></i></button>
                                    <button type="button" class="editor-btn" data-action="alignJustify" title="Justify"><i class="fas fa-align-justify"></i></button>
                                </div>
                                <div class="tiptap-group">
                                    <button type="button" class="editor-btn" data-action="link" title="Link"><i class="fas fa-link"></i></button>
                                    <button type="button" class="editor-btn" data-action="unlink" title="Remove Link"><i class="fas fa-unlink"></i></button>
                                    <label class="editor-color" title="Text Color">
                                        <i class="fas fa-palette"></i>
                                        <input type="color" id="textColorInput" />
                                    </label>
                                    <label class="editor-color" title="Highlight">
                                        <i class="fas fa-highlighter"></i>
                                        <input type="color" id="highlightColorInput" value="#fef08a" />
                                    </label>
                                </div>
                                <div class="tiptap-group">
                                    <button type="button" class="editor-btn" data-action="image" title="Insert Image"><i class="fas fa-image"></i></button>
                                    <button type="button" class="editor-btn" data-action="table" title="Insert Table"><i class="fas fa-table"></i></button>
                                    <button type="button" class="editor-btn" data-action="hr" title="Divider"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div id="tiptapEditor" class="editor-surface"></div>
                            <input type="file" id="tiptapImageInput" accept="image/*" style="display:none;">
                            <input type="hidden" name="deskripsi" id="deskripsiField" />
                        </div>
                    </div>

                    <div id="clientError" class="auth-alert auth-alert-error" style="display:none;">
                        Isi berita wajib diisi.
                    </div>

                    <div class="admin-form-actions">
                        <button type="submit" class="btn-primary" style="padding:0.75rem 1.5rem; border-radius:0.75rem;">Simpan</button>
                        <a href="/admin/informasi.php" style="color:#64748b; text-decoration:none;">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="module">
import { Editor } from 'https://esm.sh/@tiptap/core@2';
import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2';
import Underline from 'https://esm.sh/@tiptap/extension-underline@2';
import Link from 'https://esm.sh/@tiptap/extension-link@2';
import TextStyle from 'https://esm.sh/@tiptap/extension-text-style@2';
import Color from 'https://esm.sh/@tiptap/extension-color@2';
import Highlight from 'https://esm.sh/@tiptap/extension-highlight@2';
import TextAlign from 'https://esm.sh/@tiptap/extension-text-align@2';
import Image from 'https://esm.sh/@tiptap/extension-image@2';
import Table from 'https://esm.sh/@tiptap/extension-table@2';
import TableRow from 'https://esm.sh/@tiptap/extension-table-row@2';
import TableHeader from 'https://esm.sh/@tiptap/extension-table-header@2';
import TableCell from 'https://esm.sh/@tiptap/extension-table-cell@2';
import Placeholder from 'https://esm.sh/@tiptap/extension-placeholder@2';

const descField = document.getElementById('deskripsiField');
const beritaForm = document.getElementById('beritaForm');
const clientError = document.getElementById('clientError');
const headingSelect = document.getElementById('headingSelect');
const textColorInput = document.getElementById('textColorInput');
const highlightColorInput = document.getElementById('highlightColorInput');
const tiptapImageInput = document.getElementById('tiptapImageInput');
const toolbar = document.getElementById('tiptapToolbar');
const gambarInput = document.getElementById('gambarInput');
const gambarPreview = document.getElementById('gambarPreview');
const gambarPreviewText = document.getElementById('gambarPreviewText');
const gambarFileName = document.getElementById('gambarFileName');

const initialContent = <?= json_encode($deskripsi ?: '') ?>;

const editor = new Editor({
    element: document.getElementById('tiptapEditor'),
    content: initialContent,
    extensions: [
        StarterKit,
        Underline,
        Link.configure({
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
            HTMLAttributes: { rel: 'noopener noreferrer', target: '_blank' }
        }),
        TextStyle,
        Color,
        Highlight.configure({ multicolor: true }),
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        Image.configure({ inline: false, allowBase64: false }),
        Table.configure({ resizable: true }),
        TableRow,
        TableHeader,
        TableCell,
        Placeholder.configure({ placeholder: <?= json_encode($editorPlaceholder) ?> })
    ],
    onUpdate: ({ editor }) => {
        descField.value = editor.getHTML();
        if (clientError) {
            clientError.style.display = 'none';
        }
    }
});

function isEditorEmpty() {
    return editor.getText().trim() === '';
}

descField.value = editor.getHTML();

if (headingSelect) {
    headingSelect.addEventListener('change', (e) => {
        const value = e.target.value;
        if (value === 'p') {
            editor.chain().focus().setParagraph().run();
            return;
        }
        const level = Number(value.replace('h', ''));
        editor.chain().focus().setHeading({ level }).run();
    });
}

if (textColorInput) {
    textColorInput.addEventListener('input', (e) => {
        editor.chain().focus().setColor(e.target.value).run();
    });
}

if (highlightColorInput) {
    highlightColorInput.addEventListener('input', (e) => {
        editor.chain().focus().setHighlight({ color: e.target.value }).run();
    });
}

if (toolbar) {
    toolbar.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) {
            return;
        }
        const action = button.dataset.action;

        switch (action) {
            case 'bold':
                editor.chain().focus().toggleBold().run();
                break;
            case 'italic':
                editor.chain().focus().toggleItalic().run();
                break;
            case 'underline':
                editor.chain().focus().toggleUnderline().run();
                break;
            case 'strike':
                editor.chain().focus().toggleStrike().run();
                break;
            case 'codeBlock':
                editor.chain().focus().toggleCodeBlock().run();
                break;
            case 'bulletList':
                editor.chain().focus().toggleBulletList().run();
                break;
            case 'orderedList':
                editor.chain().focus().toggleOrderedList().run();
                break;
            case 'blockquote':
                editor.chain().focus().toggleBlockquote().run();
                break;
            case 'alignLeft':
                editor.chain().focus().setTextAlign('left').run();
                break;
            case 'alignCenter':
                editor.chain().focus().setTextAlign('center').run();
                break;
            case 'alignRight':
                editor.chain().focus().setTextAlign('right').run();
                break;
            case 'alignJustify':
                editor.chain().focus().setTextAlign('justify').run();
                break;
            case 'link': {
                const url = window.prompt('Masukkan URL');
                if (!url) {
                    return;
                }
                editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                break;
            }
            case 'unlink':
                editor.chain().focus().unsetLink().run();
                break;
            case 'image':
                if (tiptapImageInput) {
                    tiptapImageInput.click();
                }
                break;
            case 'table':
                editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
                break;
            case 'hr':
                editor.chain().focus().setHorizontalRule().run();
                break;
            default:
                break;
        }
    });
}

if (tiptapImageInput) {
    tiptapImageInput.addEventListener('change', async (event) => {
        const file = event.target.files && event.target.files[0];
        if (!file) {
            return;
        }

        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch('/admin/upload-image.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                window.alert(data.message || 'Gagal mengunggah gambar.');
                return;
            }
            editor.chain().focus().setImage({ src: data.url, alt: file.name }).run();
        } catch (error) {
            window.alert('Gagal mengunggah gambar.');
        } finally {
            tiptapImageInput.value = '';
        }
    });
}

if (beritaForm) {
    beritaForm.addEventListener('submit', (e) => {
        if (isEditorEmpty()) {
            if (clientError) {
                clientError.style.display = 'block';
            }
            e.preventDefault();
            editor.commands.focus();
            return;
        }
        descField.value = editor.getHTML();
    });
}

if (gambarInput) {
    gambarInput.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) {
            gambarPreview.style.display = 'none';
            gambarPreviewText.style.display = 'block';
            gambarFileName.textContent = 'Belum ada file';
            gambarPreviewText.textContent = 'No preview';
            return;
        }

        gambarFileName.textContent = file.name;
        if (!file.type.startsWith('image/')) {
            gambarPreview.style.display = 'none';
            gambarPreviewText.style.display = 'block';
            gambarPreviewText.textContent = 'File bukan gambar';
            return;
        }

        const reader = new FileReader();
        reader.onload = (evt) => {
            gambarPreview.src = evt.target.result;
            gambarPreview.style.display = 'block';
            gambarPreviewText.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
}
</script>

<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
</style>
</body>
</html>
