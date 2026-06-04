<?php
$adminTitle = 'Kelola Kategori';
include __DIR__ . '/includes/header.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $nama = trim($_POST['nama'] ?? '');
            $icon = trim($_POST['icon'] ?? 'fas fa-folder');
            $warna = trim($_POST['warna'] ?? '#6366f1');
            if (!empty($nama)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama), '-'));
                $stmt = $conn->prepare("INSERT INTO categories (nama, slug, icon, warna) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama, $slug, $icon, $warna);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/categories.php?msg=added');
            exit;
        }

        if ($action === 'edit' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $nama = trim($_POST['nama'] ?? '');
            $icon = trim($_POST['icon'] ?? 'fas fa-folder');
            $warna = trim($_POST['warna'] ?? '#6366f1');
            if (!empty($nama)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama), '-'));
                $stmt = $conn->prepare("UPDATE categories SET nama=?, slug=?, icon=?, warna=? WHERE id=?");
                $stmt->bind_param("ssssi", $nama, $slug, $icon, $warna, $id);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/categories.php?msg=updated');
            exit;
        }

        if ($action === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $conn->query("DELETE FROM categories WHERE id = $id");
            header('Location: /admin/categories.php?msg=deleted');
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        // Handle duplicate slug
        if ($e->getCode() == 1062) {
            header('Location: /admin/categories.php?msg=duplicate');
            exit;
        } else {
            throw $e;
        }
    }
}

$categories = $conn->query("
    SELECT c.*, COUNT(i.id) as total_info
    FROM categories c
    LEFT JOIN informasi i ON c.id = i.category_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Kelola Kategori</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;">Tambah, edit, dan hapus kategori informasi</p>
            </div>
        </div>
        <button onclick="openAddModal()" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem; font-size:0.8125rem;">
            <i class="fas fa-plus"></i> Tambah Kategori
        </button>
    </div>

    <div class="admin-content">
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'duplicate'): ?>
            <div class="auth-alert auth-alert-error" style="max-width:400px; margin-bottom:1rem;">
                <i class="fas fa-exclamation-circle"></i>
                Kategori dengan nama tersebut sudah ada.
            </div>
            <?php else: ?>
            <div class="auth-alert auth-alert-success" style="max-width:400px; margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i>
                Berhasil!
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Categories Grid -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1rem;">
            <?php while ($cat = $categories->fetch_assoc()): ?>
            <div class="stat-card" style="--card-accent:<?= $cat['warna'] ?>;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <div style="width:2.75rem; height:2.75rem; border-radius:0.75rem; background:<?= $cat['warna'] ?>15; display:flex; align-items:center; justify-content:center;">
                            <i class="<?= htmlspecialchars($cat['icon']) ?>" style="color:<?= $cat['warna'] ?>; font-size:1.125rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight:600; color: #e2e8f0; font-size:0.9375rem;"><?= htmlspecialchars($cat['nama']) ?></div>
                            <div style="font-size:0.6875rem; color:#64748b;"><?= $cat['total_info'] ?> informasi</div>
                        </div>
                    </div>
                    <div style="display:flex; gap:0.375rem;">
                        <button onclick='openEditModal(<?= json_encode($cat) ?>)' class="btn-action btn-edit" style="padding:0.375rem;"><i class="fas fa-pen" style="font-size:0.6875rem;"></i></button>
                        <form method="POST" onsubmit="return confirm('Hapus kategori ini?')" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn-action btn-delete" style="padding:0.375rem;"><i class="fas fa-trash-alt" style="font-size:0.6875rem;"></i></button>
                        </form>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <span style="font-size:0.6875rem; color:#475569; background:rgba(255,255,255,0.03); padding:0.25rem 0.5rem; border-radius:0.375rem;"><i class="fas fa-link" style="margin-right:0.25rem;"></i><?= htmlspecialchars($cat['slug']) ?></span>
                    <span style="font-size:0.6875rem; color:#475569; background:rgba(255,255,255,0.03); padding:0.25rem 0.5rem; border-radius:0.375rem;">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:2px; background:<?= $cat['warna'] ?>; margin-right:0.25rem; vertical-align:middle;"></span>
                        <?= $cat['warna'] ?>
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="admin-modal-overlay" id="categoryModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 style="font-size:1rem; font-weight:600; color: #e2e8f0; margin:0;" id="modalTitle">Tambah Kategori</h3>
            <button onclick="closeModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:1.25rem;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="categoryForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="formId">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Nama Kategori</label>
                    <input type="text" name="nama" id="formNama" class="auth-input" style="padding-left:1rem;" placeholder="Nama kategori" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Pilih Ikon</label>
                    <input type="hidden" name="icon" id="formIcon" value="fas fa-folder">
                    <div style="display:grid; grid-template-columns:repeat(8, 1fr); gap:0.5rem;" id="iconGrid">
                        <?php
                        $icons = [
                            'fas fa-folder', 'fas fa-newspaper', 'fas fa-bullhorn', 'fas fa-calendar-alt',
                            'fas fa-trophy', 'fas fa-graduation-cap', 'fas fa-briefcase', 'fas fa-book',
                            'fas fa-users', 'fas fa-laptop-code', 'fas fa-heart', 'fas fa-globe',
                            'fas fa-lightbulb', 'fas fa-star', 'fas fa-camera', 'fas fa-music'
                        ];
                        foreach ($icons as $i): ?>
                        <div class="icon-option" data-icon="<?= $i ?>" onclick="selectIcon('<?= $i ?>')" style="height:2.5rem; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:0.5rem; cursor:pointer; color:#94a3b8; transition:all 0.2s;">
                            <i class="<?= $i ?>"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Warna</label>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <input type="color" name="warna" id="formWarna" value="#6366f1" style="width:3rem; height:2.5rem; border:none; border-radius:0.5rem; cursor:pointer; background:transparent;">
                        <input type="text" id="formWarnaText" class="auth-input" style="padding-left:1rem; flex:1;" value="#6366f1" readonly>
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" onclick="closeModal()" class="btn-action" style="background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); padding:0.5rem 1rem;">Batal</button>
                <button type="submit" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem;" id="submitBtn">Tambah</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectIcon(iconClass) {
    document.getElementById('formIcon').value = iconClass;
    document.querySelectorAll('.icon-option').forEach(el => {
        if (el.dataset.icon === iconClass) {
            el.style.borderColor = '#818cf8';
            el.style.color = '#818cf8';
            el.style.background = 'rgba(99,102,241,0.1)';
        } else {
            el.style.borderColor = 'rgba(255,255,255,0.08)';
            el.style.color = '#94a3b8';
            el.style.background = 'rgba(255,255,255,0.03)';
        }
    });
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Kategori';
    document.getElementById('formAction').value = 'add';
    document.getElementById('formId').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formWarna').value = '#6366f1';
    document.getElementById('formWarnaText').value = '#6366f1';
    document.getElementById('submitBtn').textContent = 'Tambah';
    selectIcon('fas fa-folder');
    document.getElementById('categoryModal').classList.add('show');
}

function openEditModal(cat) {
    document.getElementById('modalTitle').textContent = 'Edit Kategori';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formId').value = cat.id;
    document.getElementById('formNama').value = cat.nama;
    document.getElementById('formWarna').value = cat.warna;
    document.getElementById('formWarnaText').value = cat.warna;
    document.getElementById('submitBtn').textContent = 'Simpan';
    selectIcon(cat.icon);
    document.getElementById('categoryModal').classList.add('show');
}

function closeModal() { document.getElementById('categoryModal').classList.remove('show'); }

document.getElementById('formWarna').addEventListener('input', function() {
    document.getElementById('formWarnaText').value = this.value;
});

</script>
<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
.icon-option:hover { border-color: rgba(255,255,255,0.15) !important; color: #e2e8f0 !important; }
</style>
</body>
</html>
