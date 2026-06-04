<?php
$pageTitle = 'Tulis Berita';
require_once __DIR__ . '/../config/database.php';
requireLogin();
if (!isPublisher()) {
    header('Location: /');
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $sumber = trim($_POST['sumber'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $deskripsi = $_POST['deskripsi'] ?? '';

    if ($judul === '') {
        $errors[] = 'Judul wajib diisi.';
    }
    if ($sumber === '') {
        $errors[] = 'Sumber penerbit wajib diisi.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }
    if (trim(strip_tags($deskripsi)) === '') {
        $errors[] = 'Isi berita wajib diisi.';
    }

    $gambarName = null;
    if (!empty($_FILES['gambar']['name'])) {
        $uploadDir = __DIR__ . '/../foto/';
        $tmpName = $_FILES['gambar']['tmp_name'];
        $fileInfo = @getimagesize($tmpName);
        if ($fileInfo === false) {
            $errors[] = 'File gambar tidak valid.';
        } else {
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
        $stmt = $conn->prepare('INSERT INTO informasi (judul, slug, sumber, deskripsi, gambar, deadline, category_id, user_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")');
        $userId = (int)$_SESSION['user_id'];
        $stmt->bind_param('ssssssii', $judul, $slug, $sumber, $cleanHtml, $gambarName, $deadline, $categoryId, $userId);
        $stmt->execute();
        $stmt->close();

        header('Location: /publisher/index.php?msg=created');
        exit;
    }
}

$categories = $conn->query('SELECT id, nama FROM categories ORDER BY nama ASC');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="pt-28 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold font-space text-dark-100">Tulis Berita</h1>
            <p class="text-dark-400 mt-2">Gunakan editor untuk membuat berita kampus.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                <?php foreach ($errors as $err): ?>
                    <div><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="publisher-compose">
            <div class="publisher-field">
                <label for="judulInput">Judul Berita</label>
                <input id="judulInput" type="text" name="judul" value="<?= htmlspecialchars($judul) ?>" class="publisher-input" placeholder="Masukkan judul berita">
            </div>

            <div class="publisher-field">
                <label for="sumberInput">Sumber Penerbit</label>
                <input id="sumberInput" type="text" name="sumber" value="<?= htmlspecialchars($sumber) ?>" class="publisher-input" placeholder="Contoh: FDA, FDD">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="publisher-field">
                    <label for="categoryInput">Kategori</label>
                    <select id="categoryInput" name="category_id" class="publisher-input">
                        <option value="">Pilih kategori</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="publisher-field">
                    <label for="deadlineInput">Deadline (Opsional)</label>
                    <input id="deadlineInput" type="date" name="deadline" value="<?= htmlspecialchars($deadline) ?>" class="publisher-input">
                </div>
            </div>

            <div class="publisher-field">
                <label for="gambarInput">Gambar (Opsional)</label>
                <div class="publisher-upload">
                    <div class="upload-icon"><i class="fas fa-image"></i></div>
                    <div>
                        <div class="upload-title">Upload gambar utama</div>
                        <div class="upload-hint">JPG, PNG, atau WEBP untuk thumbnail berita.</div>
                        <input id="gambarInput" type="file" name="gambar" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="publisher-field">
                <label for="tiptapEditor">Isi Berita</label>
                <div class="editor-shell">
                    <div class="tiptap-toolbar" id="tiptapToolbar">
                        <div class="tiptap-group">
                            <button type="button" class="editor-btn" data-action="bold" title="Bold"><i class="fas fa-bold"></i></button>
                            <button type="button" class="editor-btn" data-action="italic" title="Italic"><i class="fas fa-italic"></i></button>
                            <button type="button" class="editor-btn" data-action="underline" title="Underline"><i class="fas fa-underline"></i></button>
                            <button type="button" class="editor-btn" data-action="strike" title="Strike"><i class="fas fa-strikethrough"></i></button>
                            <button type="button" class="editor-btn" data-action="codeBlock" title="Code block"><i class="fas fa-code"></i></button>
                        </div>
                        <div class="tiptap-group">
                            <button type="button" class="editor-btn" data-action="bulletList" title="Bullet list"><i class="fas fa-list-ul"></i></button>
                            <button type="button" class="editor-btn" data-action="orderedList" title="Numbered list"><i class="fas fa-list-ol"></i></button>
                            <button type="button" class="editor-btn" data-action="blockquote" title="Quote"><i class="fas fa-quote-right"></i></button>
                        </div>
                        <div class="tiptap-group">
                            <select id="headingSelect" class="tiptap-select" data-action="heading">
                                <option value="p">Paragraph</option>
                                <option value="h2">Heading 2</option>
                                <option value="h3">Heading 3</option>
                                <option value="h4">Heading 4</option>
                            </select>
                            <button type="button" class="editor-btn" data-action="alignLeft" title="Align left"><i class="fas fa-align-left"></i></button>
                            <button type="button" class="editor-btn" data-action="alignCenter" title="Align center"><i class="fas fa-align-center"></i></button>
                            <button type="button" class="editor-btn" data-action="alignRight" title="Align right"><i class="fas fa-align-right"></i></button>
                            <button type="button" class="editor-btn" data-action="alignJustify" title="Justify"><i class="fas fa-align-justify"></i></button>
                        </div>
                        <div class="tiptap-group">
                            <button type="button" class="editor-btn" data-action="link" title="Link"><i class="fas fa-link"></i></button>
                            <button type="button" class="editor-btn" data-action="unlink" title="Remove link"><i class="fas fa-unlink"></i></button>
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
                            <button type="button" class="editor-btn" data-action="image" title="Insert image"><i class="fas fa-image"></i></button>
                            <button type="button" class="editor-btn" data-action="table" title="Insert table"><i class="fas fa-table"></i></button>
                            <button type="button" class="editor-btn" data-action="hr" title="Divider"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div id="tiptapEditor" class="tiptap-surface"></div>
                    <div class="editor-status">
                        <span>TipTap editor</span>
                        <span id="editorWordCount">0 kata</span>
                    </div>
                    <input type="file" id="tiptapImageInput" accept="image/*" style="display:none;">
                    <input type="hidden" name="deskripsi" id="deskripsiField" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 text-dark-100 font-semibold shadow-lg shadow-primary-600/20">Kirim untuk Review</button>
                <a href="/publisher/index.php" class="text-dark-300 hover:text-dark-100">Batal</a>
            </div>
        </form>
    </div>
</section>

<style>
.publisher-compose {
    display: grid;
    gap: 1.5rem;
    background: #ffffff;
    border: 1px solid rgba(15,23,42,0.08);
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 18px 40px rgba(15,23,42,0.08);
}

.publisher-field label {
    display: block;
    color: #334155;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.publisher-input {
    width: 100%;
    border: 1px solid rgba(15,23,42,0.12);
    border-radius: 0.875rem;
    background: #f8fafc;
    color: #0f172a;
    padding: 0.8rem 1rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}

.publisher-input:focus {
    background: #ffffff;
    border-color: rgba(99,102,241,0.5);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
}

.publisher-upload {
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px dashed rgba(15,23,42,0.2);
    background: #f8fafc;
    border-radius: 0.875rem;
    padding: 1rem;
}

.upload-icon {
    width: 3rem;
    height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background: rgba(99,102,241,0.1);
    color: #4f46e5;
    flex-shrink: 0;
}

.upload-title { color: #0f172a; font-size: 0.875rem; font-weight: 700; }
.upload-hint { color: #64748b; font-size: 0.75rem; margin: 0.15rem 0 0.5rem; }
.publisher-upload input { color: #475569; font-size: 0.8125rem; }

.editor-shell {
    overflow: hidden;
    border: 1px solid rgba(15,23,42,0.12);
    border-radius: 1rem;
    background: #ffffff;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
}

.tiptap-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding: 0.75rem;
    border-bottom: 1px solid rgba(15,23,42,0.08);
    background: #f8fafc;
}

.tiptap-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    align-items: center;
    padding-right: 0.25rem;
    border-right: 1px solid rgba(15,23,42,0.08);
}

.tiptap-group:last-child { border-right: 0; }

.tiptap-select {
    min-height: 2.25rem;
    border-radius: 0.625rem;
    background: #ffffff;
    border: 1px solid rgba(15,23,42,0.12);
    color: #334155;
    padding: 0.35rem 0.6rem;
    font-size: 0.8125rem;
    outline: none;
}

.editor-btn {
    min-width: 2.25rem;
    height: 2.25rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 1px solid rgba(15,23,42,0.12);
    color: #334155;
    border-radius: 0.625rem;
    padding: 0 0.65rem;
    font-size: 0.8125rem;
    cursor: pointer;
    transition: transform 0.2s, background 0.2s, border-color 0.2s, color 0.2s, box-shadow 0.2s;
}

.editor-btn:hover,
.editor-btn.is-active {
    background: #eef2ff;
    border-color: rgba(99,102,241,0.35);
    color: #4338ca;
    box-shadow: 0 4px 12px rgba(99,102,241,0.12);
}

.editor-btn:hover { transform: translateY(-1px); }

.editor-color {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    min-height: 2.25rem;
    padding: 0.25rem 0.5rem;
    border: 1px solid rgba(15,23,42,0.12);
    border-radius: 0.625rem;
    background: #ffffff;
    color: #334155;
    font-size: 0.75rem;
    cursor: pointer;
}

.editor-color input { border: 0; padding: 0; width: 1.75rem; height: 1.75rem; background: transparent; }

.tiptap-surface {
    min-height: 360px;
    padding: 1.25rem;
    color: #0f172a;
    background: #ffffff;
}

.ProseMirror {
    outline: none;
    min-height: 320px;
    color: #0f172a;
    line-height: 1.75;
    font-size: 0.96rem;
}

.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    color: #94a3b8;
    float: left;
    height: 0;
    pointer-events: none;
}

.ProseMirror p { margin: 0 0 0.9rem; }
.ProseMirror h2 { font-size: 1.65rem; }
.ProseMirror h3 { font-size: 1.35rem; }
.ProseMirror h4 { font-size: 1.1rem; }
.ProseMirror h2, .ProseMirror h3, .ProseMirror h4 {
    margin: 1.25rem 0 0.75rem;
    line-height: 1.28;
    color: #0f172a;
    font-weight: 800;
}

.ProseMirror blockquote {
    border-left: 4px solid #6366f1;
    background: #f8fafc;
    margin: 1rem 0;
    padding: 0.75rem 1rem;
    color: #475569;
    border-radius: 0 0.75rem 0.75rem 0;
}

.ProseMirror ul, .ProseMirror ol { padding-left: 1.5rem; margin: 0 0 0.75rem; }

.ProseMirror pre {
    background: #0f172a;
    color: #e2e8f0;
    padding: 0.9rem 1rem;
    border-radius: 0.875rem;
    overflow-x: auto;
}

.ProseMirror code {
    background: #e2e8f0;
    color: #0f172a;
    padding: 0.1rem 0.35rem;
    border-radius: 0.35rem;
}

.ProseMirror img {
    max-width: 100%;
    height: auto;
    border-radius: 0.875rem;
    border: 1px solid rgba(15,23,42,0.08);
}

.ProseMirror table { border-collapse: collapse; margin: 0.75rem 0; width: 100%; }
.ProseMirror th, .ProseMirror td { border: 1px solid rgba(15,23,42,0.12); padding: 0.6rem; text-align: left; }
.ProseMirror th { background: #f1f5f9; }
.ProseMirror hr { border: 0; border-top: 1px solid rgba(15,23,42,0.12); margin: 1.5rem 0; }

.editor-status {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.65rem 1rem;
    border-top: 1px solid rgba(15,23,42,0.08);
    background: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
}

@media (max-width: 640px) {
    .publisher-compose { padding: 1rem; }
    .tiptap-toolbar { gap: 0.5rem; }
    .tiptap-group { border-right: 0; padding-right: 0; }
    .tiptap-surface { min-height: 300px; padding: 1rem; }
}
</style>

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
const toolbar = document.getElementById('tiptapToolbar');
const headingSelect = document.getElementById('headingSelect');
const textColorInput = document.getElementById('textColorInput');
const highlightColorInput = document.getElementById('highlightColorInput');
const tiptapImageInput = document.getElementById('tiptapImageInput');
const editorWordCount = document.getElementById('editorWordCount');

const initialContent = <?= json_encode($deskripsi ?: '') ?>;
let editor;

const refreshToolbar = () => {
    if (!toolbar || !editor) {
        return;
    }

    const activeChecks = {
        bold: () => editor.isActive('bold'),
        italic: () => editor.isActive('italic'),
        underline: () => editor.isActive('underline'),
        strike: () => editor.isActive('strike'),
        codeBlock: () => editor.isActive('codeBlock'),
        bulletList: () => editor.isActive('bulletList'),
        orderedList: () => editor.isActive('orderedList'),
        blockquote: () => editor.isActive('blockquote'),
        alignLeft: () => editor.isActive({ textAlign: 'left' }),
        alignCenter: () => editor.isActive({ textAlign: 'center' }),
        alignRight: () => editor.isActive({ textAlign: 'right' }),
        alignJustify: () => editor.isActive({ textAlign: 'justify' }),
        link: () => editor.isActive('link')
    };

    toolbar.querySelectorAll('[data-action]').forEach((button) => {
        const check = activeChecks[button.dataset.action];
        button.classList.toggle('is-active', Boolean(check && check()));
    });

    if (headingSelect) {
        if (editor.isActive('heading', { level: 2 })) headingSelect.value = 'h2';
        else if (editor.isActive('heading', { level: 3 })) headingSelect.value = 'h3';
        else if (editor.isActive('heading', { level: 4 })) headingSelect.value = 'h4';
        else headingSelect.value = 'p';
    }

    if (editorWordCount) {
        const words = editor.getText().trim().split(/\s+/).filter(Boolean).length;
        editorWordCount.textContent = `${words} kata`;
    }
};

editor = new Editor({
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
        Placeholder.configure({ placeholder: 'Tulis isi berita di sini...' })
    ],
    onUpdate: ({ editor }) => {
        descField.value = editor.getHTML();
        refreshToolbar();
    },
    onSelectionUpdate: () => {
        refreshToolbar();
    }
});

descField.value = editor.getHTML();
refreshToolbar();

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

        refreshToolbar();
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
            const response = await fetch('/publisher/upload-image.php', {
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
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
