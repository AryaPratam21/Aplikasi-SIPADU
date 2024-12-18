<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/auth/login.php');
}

$db = (new Database())->getConnection();
$error = '';
$success = '';

// Tambah user baru
if (isset($_POST['add_user'])) {
    try {
        if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['role'])) {
            throw new Exception("Semua field harus diisi!");
        }

        // Cek username sudah ada atau belum
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Username sudah digunakan!");
        }

        // Hash password
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Insert user baru
        $query = "INSERT INTO users (username, password, role, nama_lengkap, email) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['username'],
            $hashedPassword,
            $_POST['role'],
            $_POST['nama_lengkap'],
            $_POST['email']
        ]);

        logActivity($db, 'Menambah user baru: ' . $_POST['username']);
        $success = "User berhasil ditambahkan!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Update user
if (isset($_POST['edit_user'])) {
    try {
        $userId = (int)$_POST['user_id'];
        
        // Update password jika diisi
        if (!empty($_POST['new_password'])) {
            $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $query = "UPDATE users SET 
                      username = ?, 
                      password = ?,
                      role = ?,
                      nama_lengkap = ?,
                      email = ?
                      WHERE id = ?";
            $params = [
                $_POST['username'],
                $hashedPassword,
                $_POST['role'],
                $_POST['nama_lengkap'],
                $_POST['email'],
                $userId
            ];
        } else {
            $query = "UPDATE users SET 
                      username = ?,
                      role = ?,
                      nama_lengkap = ?,
                      email = ?
                      WHERE id = ?";
            $params = [
                $_POST['username'],
                $_POST['role'],
                $_POST['nama_lengkap'],
                $_POST['email'],
                $userId
            ];
        }

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        logActivity($db, 'Mengupdate user: ' . $_POST['username']);
        $success = "Data user berhasil diupdate!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Hapus user
if (isset($_GET['delete'])) {
    try {
        $userId = (int)$_GET['delete'];
        
        // Cek apakah user yang akan dihapus bukan user yang sedang login
        if ($userId === $_SESSION['user_id']) {
            throw new Exception("Tidak dapat menghapus akun yang sedang digunakan!");
        }

        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        logActivity($db, 'Menghapus user ID: ' . $userId);
        $success = "User berhasil dihapus!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Ambil daftar user
try {
    $query = "SELECT * FROM users ORDER BY username";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Pengaturan User</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        Tambah User
    </button>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Terakhir Login</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" 
                                    onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                Edit
                            </button>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                            <a href="?delete=<?= $user['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Yakin ingin menghapus user ini?')">
                                Hapus
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="add_user" value="1">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" id="edit_nama_lengkap" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" id="edit_role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_nama_lengkap').value = user.nama_lengkap;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
</script>

<?php include '../../includes/footer.php'; ?>
