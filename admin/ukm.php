<?php
$adminTitle = 'Kelola UKM';
include __DIR__ . '/includes/header.php';

function slugifyText($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'ukm';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add') {
            $nama = trim($_POST['nama'] ?? '');
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            if ($nama !== '') {
                $slug = slugifyText($nama);
                $stmt = $conn->prepare("INSERT INTO ukm (nama, slug, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $nama, $slug, $isActive);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/ukm.php?msg=added');
            exit;
        }

        if ($action === 'edit' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $nama = trim($_POST['nama'] ?? '');
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            if ($nama !== '') {
                $slug = slugifyText($nama);
                $stmt = $conn->prepare("UPDATE ukm SET nama = ?, slug = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssii", $nama, $slug, $isActive, $id);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/ukm.php?msg=updated');
            exit;
        }

        if ($action === 'delete' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $conn->query("DELETE FROM ukm WHERE id = $id");
            header('Location: /admin/ukm.php?msg=deleted');
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            header('Location: /admin/ukm.php?msg=duplicate');
            exit;
        }
        throw $e;
    }
}

$ukmRows = $conn->query("SELECT * FROM ukm ORDER BY nama ASC");
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Kelola UKM</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;">Tambah, edit, dan nonaktifkan UKM</p>
            </div>
        </div>
        <button onclick="openAddModal()" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem; font-size:0.8125rem;">
            <i class="fas fa-plus"></i> Tambah UKM
        </button>
    </div>

    <div class="admin-content">
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'duplicate'): ?>
            <div class="auth-alert auth-alert-error" style="max-width:400px; margin-bottom:1rem;">
                <i class="fas fa-exclamation-circle"></i>
                UKM dengan nama tersebut sudah ada.
            </div>
            <?php else: ?>
            <div class="auth-alert auth-alert-success" style="max-width:400px; margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i>
                Berhasil!
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1rem;">
            <?php while ($ukm = $ukmRows->fetch_assoc()): ?>
            <div class="stat-card" style="--card-accent:#10b981;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                    <div>
                        <div style="font-weight:600; color:#0f172a; font-size:0.9375rem;"><?= htmlspecialchars($ukm['nama']) ?></div>
                        <div style="font-size:0.6875rem; color:#64748b;">Slug: <?= htmlspecialchars($ukm['slug']) ?></div>
                    </div>
                    <span class="badge-<?= $ukm['is_active'] ? 'approved' : 'rejected' ?>">
                        <?= $ukm['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
                <div style="display:flex; gap:0.375rem;">
                    <button onclick='openEditModal(<?= json_encode($ukm) ?>)' class="btn-action btn-edit"><i class="fas fa-pen"></i></button>
                    <form method="POST" onsubmit="return confirm('Hapus UKM ini?')" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $ukm['id'] ?>">
                        <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div class="admin-modal-overlay" id="ukmModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 style="font-size:1rem; font-weight:600; color: #e2e8f0; margin:0;" id="modalTitle">Tambah UKM</h3>
            <button onclick="closeModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:1.25rem;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="ukmForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="formId">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Nama UKM</label>
                    <input type="text" name="nama" id="formNama" class="auth-input" style="padding-left:1rem;" placeholder="Nama UKM" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Status</label>
                    <select name="is_active" id="formStatus" class="auth-select" style="padding-left:1rem;">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
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
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah UKM';
    document.getElementById('formAction').value = 'add';
    document.getElementById('formId').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formStatus').value = '1';
    document.getElementById('submitBtn').textContent = 'Tambah';
    document.getElementById('ukmModal').classList.add('show');
}

function openEditModal(row) {
    document.getElementById('modalTitle').textContent = 'Edit UKM';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formId').value = row.id;
    document.getElementById('formNama').value = row.nama;
    document.getElementById('formStatus').value = row.is_active;
    document.getElementById('submitBtn').textContent = 'Simpan';
    document.getElementById('ukmModal').classList.add('show');
}

function closeModal() {
    document.getElementById('ukmModal').classList.remove('show');
}
</script>

<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
</style>
</body>
</html>
