<?php
$adminTitle = 'Kelola Fakultas & ORMAWA';
include __DIR__ . '/includes/header.php';

function slugifyText($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'item';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'add_fakultas') {
            $nama = trim($_POST['nama'] ?? '');
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            if ($nama !== '') {
                $slug = slugifyText($nama);
                $stmt = $conn->prepare("INSERT INTO fakultas (nama, slug, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $nama, $slug, $isActive);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/fakultas-ormawa.php?msg=fak_added');
            exit;
        }

        if ($action === 'edit_fakultas' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $nama = trim($_POST['nama'] ?? '');
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            if ($nama !== '') {
                $slug = slugifyText($nama);
                $stmt = $conn->prepare("UPDATE fakultas SET nama = ?, slug = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("ssii", $nama, $slug, $isActive, $id);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/fakultas-ormawa.php?msg=fak_updated');
            exit;
        }

        if ($action === 'delete_fakultas' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $conn->query("DELETE FROM fakultas WHERE id = $id");
            header('Location: /admin/fakultas-ormawa.php?msg=fak_deleted');
            exit;
        }

        if ($action === 'add_ormawa') {
            $nama = trim($_POST['nama'] ?? '');
            $scope = $_POST['scope'] ?? 'fakultas';
            $fakultasId = (int)($_POST['fakultas_id'] ?? 0);
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            if ($nama !== '') {
                $slug = slugifyText($nama);
                $scopeValue = $scope === 'univ' ? 'univ' : 'fakultas';
                $fakId = $scopeValue === 'univ' ? null : ($fakultasId > 0 ? $fakultasId : null);
                $stmt = $conn->prepare("INSERT INTO ormawa (nama, slug, scope, fakultas_id, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $nama, $slug, $scopeValue, $fakId, $isActive);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/fakultas-ormawa.php?msg=orm_added');
            exit;
        }

        if ($action === 'edit_ormawa' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $nama = trim($_POST['nama'] ?? '');
            $scope = $_POST['scope'] ?? 'fakultas';
            $fakultasId = (int)($_POST['fakultas_id'] ?? 0);
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            if ($nama !== '') {
                $slug = slugifyText($nama);
                $scopeValue = $scope === 'univ' ? 'univ' : 'fakultas';
                $fakId = $scopeValue === 'univ' ? null : ($fakultasId > 0 ? $fakultasId : null);
                $stmt = $conn->prepare("UPDATE ormawa SET nama = ?, slug = ?, scope = ?, fakultas_id = ?, is_active = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $nama, $slug, $scopeValue, $fakId, $isActive, $id);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: /admin/fakultas-ormawa.php?msg=orm_updated');
            exit;
        }

        if ($action === 'delete_ormawa' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $conn->query("DELETE FROM ormawa WHERE id = $id");
            header('Location: /admin/fakultas-ormawa.php?msg=orm_deleted');
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            header('Location: /admin/fakultas-ormawa.php?msg=duplicate');
            exit;
        }
        throw $e;
    }
}

$fakultasRows = $conn->query("SELECT * FROM fakultas ORDER BY nama ASC");
$ormawaRows = $conn->query("SELECT o.*, f.nama as fakultas_nama FROM ormawa o LEFT JOIN fakultas f ON o.fakultas_id = f.id ORDER BY o.scope ASC, o.nama ASC");

$fakultasOptions = [];
$res = $conn->query("SELECT id, nama FROM fakultas ORDER BY nama ASC");
while ($row = $res->fetch_assoc()) {
    $fakultasOptions[] = $row;
}
?>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div style="display:flex; align-items:center; gap:1rem;">
            <button onclick="toggleSidebar()" style="background:none; border:none; color:#94a3b8; font-size:1.25rem; cursor:pointer; display:none;" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color: #e2e8f0; margin:0; font-family:'Space Grotesk',sans-serif;">Kelola Fakultas & ORMAWA</h1>
                <p style="font-size:0.75rem; color:#64748b; margin:0;">Atur daftar fakultas dan ORMAWA</p>
            </div>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button onclick="openFakultasAdd()" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem; font-size:0.8125rem;">
                <i class="fas fa-plus"></i> Tambah Fakultas
            </button>
            <button onclick="openOrmawaAdd()" class="btn-action" style="padding:0.5rem 1rem; font-size:0.8125rem; background:rgba(14,165,233,0.1); color:#0ea5e9; border:1px solid rgba(14,165,233,0.2);">
                <i class="fas fa-plus"></i> Tambah ORMAWA
            </button>
        </div>
    </div>

    <div class="admin-content">
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'duplicate'): ?>
            <div class="auth-alert auth-alert-error" style="max-width:420px; margin-bottom:1rem;">
                <i class="fas fa-exclamation-circle"></i>
                Data sudah ada.
            </div>
            <?php else: ?>
            <div class="auth-alert auth-alert-success" style="max-width:420px; margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i> Berhasil!
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; margin-bottom:2rem;">
            <?php while ($fak = $fakultasRows->fetch_assoc()): ?>
            <div class="stat-card" style="--card-accent:#0ea5e9;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                    <div>
                        <div style="font-weight:600; color:#0f172a; font-size:0.9375rem;"><?= htmlspecialchars($fak['nama']) ?></div>
                        <div style="font-size:0.6875rem; color:#64748b;">Slug: <?= htmlspecialchars($fak['slug']) ?></div>
                    </div>
                    <span class="badge-<?= $fak['is_active'] ? 'approved' : 'rejected' ?>">
                        <?= $fak['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
                <div style="display:flex; gap:0.375rem;">
                    <button onclick='openFakultasEdit(<?= json_encode($fak) ?>)' class="btn-action btn-edit"><i class="fas fa-pen"></i></button>
                    <form method="POST" onsubmit="return confirm('Hapus fakultas ini?')" style="display:inline;">
                        <input type="hidden" name="action" value="delete_fakultas">
                        <input type="hidden" name="id" value="<?= $fak['id'] ?>">
                        <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <h3 style="font-size:0.9375rem; font-weight:600; color: #0f172a; margin:0;">Daftar ORMAWA</h3>
                    <div style="font-size:0.75rem; color:#64748b; margin-top:0.25rem;">Atur ORMAWA untuk UNIV atau per fakultas</div>
                </div>
            </div>
            <div class="admin-card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Scope</th>
                            <th>Fakultas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $ormawaRows->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['scope'] === 'univ' ? 'UNIV' : 'Fakultas' ?></td>
                            <td><?= $row['scope'] === 'univ' ? '-' : htmlspecialchars($row['fakultas_nama'] ?? '-') ?></td>
                            <td>
                                <span class="badge-<?= $row['is_active'] ? 'approved' : 'rejected' ?>">
                                    <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <button onclick='openOrmawaEdit(<?= json_encode($row) ?>)' class="btn-action btn-edit"><i class="fas fa-pen"></i></button>
                                <form method="POST" onsubmit="return confirm('Hapus ORMAWA ini?')" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_ormawa">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="admin-modal-overlay" id="fakultasModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 style="font-size:1rem; font-weight:600; color: #e2e8f0; margin:0;" id="fakultasModalTitle">Tambah Fakultas</h3>
            <button onclick="closeFakultasModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:1.25rem;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="fakultasForm">
            <input type="hidden" name="action" id="fakultasAction" value="add_fakultas">
            <input type="hidden" name="id" id="fakultasId">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Nama Fakultas</label>
                    <input type="text" name="nama" id="fakultasNama" class="auth-input" style="padding-left:1rem;" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Status</label>
                    <select name="is_active" id="fakultasStatus" class="auth-select" style="padding-left:1rem;">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" onclick="closeFakultasModal()" class="btn-action" style="background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); padding:0.5rem 1rem;">Batal</button>
                <button type="submit" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem;" id="fakultasSubmit">Tambah</button>
            </div>
        </form>
    </div>
</div>

<div class="admin-modal-overlay" id="ormawaModal">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3 style="font-size:1rem; font-weight:600; color: #e2e8f0; margin:0;" id="ormawaModalTitle">Tambah ORMAWA</h3>
            <button onclick="closeOrmawaModal()" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:1.25rem;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="ormawaForm">
            <input type="hidden" name="action" id="ormawaAction" value="add_ormawa">
            <input type="hidden" name="id" id="ormawaId">
            <div class="admin-modal-body">
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Nama ORMAWA</label>
                    <input type="text" name="nama" id="ormawaNama" class="auth-input" style="padding-left:1rem;" required>
                </div>
                <div class="form-group">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Scope</label>
                    <select name="scope" id="ormawaScope" class="auth-select" style="padding-left:1rem;">
                        <option value="univ">UNIV</option>
                        <option value="fakultas" selected>Fakultas</option>
                    </select>
                </div>
                <div class="form-group" id="ormawaFakultasGroup">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Fakultas</label>
                    <select name="fakultas_id" id="ormawaFakultas" class="auth-select" style="padding-left:1rem;">
                        <option value="">-- Pilih Fakultas --</option>
                        <?php foreach ($fakultasOptions as $fak): ?>
                        <option value="<?= $fak['id'] ?>"><?= htmlspecialchars($fak['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label style="color:#94a3b8; font-size:0.8125rem; display:block; margin-bottom:0.375rem;">Status</label>
                    <select name="is_active" id="ormawaStatus" class="auth-select" style="padding-left:1rem;">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" onclick="closeOrmawaModal()" class="btn-action" style="background:rgba(255,255,255,0.05); color:#94a3b8; border:1px solid rgba(255,255,255,0.1); padding:0.5rem 1rem;">Batal</button>
                <button type="submit" class="btn-action btn-primary-sm" style="padding:0.5rem 1rem;" id="ormawaSubmit">Tambah</button>
            </div>
        </form>
    </div>
</div>

<script>
function openFakultasAdd() {
    document.getElementById('fakultasModalTitle').textContent = 'Tambah Fakultas';
    document.getElementById('fakultasAction').value = 'add_fakultas';
    document.getElementById('fakultasId').value = '';
    document.getElementById('fakultasNama').value = '';
    document.getElementById('fakultasStatus').value = '1';
    document.getElementById('fakultasSubmit').textContent = 'Tambah';
    document.getElementById('fakultasModal').classList.add('show');
}

function openFakultasEdit(row) {
    document.getElementById('fakultasModalTitle').textContent = 'Edit Fakultas';
    document.getElementById('fakultasAction').value = 'edit_fakultas';
    document.getElementById('fakultasId').value = row.id;
    document.getElementById('fakultasNama').value = row.nama;
    document.getElementById('fakultasStatus').value = row.is_active;
    document.getElementById('fakultasSubmit').textContent = 'Simpan';
    document.getElementById('fakultasModal').classList.add('show');
}

function closeFakultasModal() {
    document.getElementById('fakultasModal').classList.remove('show');
}

function toggleOrmawaFakultas(scope) {
    const group = document.getElementById('ormawaFakultasGroup');
    group.style.display = scope === 'fakultas' ? 'block' : 'none';
}

function openOrmawaAdd() {
    document.getElementById('ormawaModalTitle').textContent = 'Tambah ORMAWA';
    document.getElementById('ormawaAction').value = 'add_ormawa';
    document.getElementById('ormawaId').value = '';
    document.getElementById('ormawaNama').value = '';
    document.getElementById('ormawaScope').value = 'fakultas';
    document.getElementById('ormawaFakultas').value = '';
    document.getElementById('ormawaStatus').value = '1';
    document.getElementById('ormawaSubmit').textContent = 'Tambah';
    toggleOrmawaFakultas('fakultas');
    document.getElementById('ormawaModal').classList.add('show');
}

function openOrmawaEdit(row) {
    document.getElementById('ormawaModalTitle').textContent = 'Edit ORMAWA';
    document.getElementById('ormawaAction').value = 'edit_ormawa';
    document.getElementById('ormawaId').value = row.id;
    document.getElementById('ormawaNama').value = row.nama;
    document.getElementById('ormawaScope').value = row.scope;
    document.getElementById('ormawaFakultas').value = row.fakultas_id || '';
    document.getElementById('ormawaStatus').value = row.is_active;
    document.getElementById('ormawaSubmit').textContent = 'Simpan';
    toggleOrmawaFakultas(row.scope);
    document.getElementById('ormawaModal').classList.add('show');
}

function closeOrmawaModal() {
    document.getElementById('ormawaModal').classList.remove('show');
}

const ormawaScope = document.getElementById('ormawaScope');
if (ormawaScope) {
    ormawaScope.addEventListener('change', (e) => {
        toggleOrmawaFakultas(e.target.value);
    });
}
</script>

<style>
@media (max-width: 1024px) { #sidebarToggleBtn { display: block !important; } }
</style>
</body>
</html>
