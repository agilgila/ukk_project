<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_tugas'])) {
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $prioritas = $_POST['prioritas'];
    $status = $_POST['status'];
    $tempo = $_POST['tempo'];

    if (!empty($nama)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tasks (nama, deskripsi, prioritas, status, tempo) 
                                   VALUES (:nama, :deskripsi, :prioritas, :status, :tempo)");
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':prioritas', $prioritas);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tempo', $tempo);
            $stmt->execute();
            $_SESSION['success'] = "Tugas berhasil ditambahkan!";
            header('Location: index.php');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

if (isset($_GET['update_status'])) {
    $id = $_GET['update_status'];
    try {
        $stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task) {
            $new_status = $task['status'] == 1 ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE tasks SET status = :new_status WHERE id = :id");
            $stmt->bindParam(':new_status', $new_status);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }

        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_GET['delete_task'])) {
    $id = $_GET['delete_task'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $_SESSION['success'] = "Tugas berhasil dihapus!";
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

$task = null;
if (isset($_GET['edit_tugas'])) {
    $id = $_GET['edit_tugas'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}


if (isset($_POST['edit_tugas'])) {
    $id = $_POST['id'];
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $prioritas = $_POST['prioritas'];
    $status = $_POST['status'];
    $tempo = $_POST['tempo'];


    if (!empty($nama)) {
        try {
        $stmt = $pdo->prepare("UPDATE tasks 
                            SET nama = :nama, deskripsi = :deskripsi, prioritas = :prioritas, status = :status, tempo = :tempo 
                            WHERE id = :id");

            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':prioritas', $prioritas);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':tempo', $tempo);
            $stmt->execute();
            $_SESSION['success'] = "Tugas berhasil diperbarui!";

            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}


if (isset($_GET['batal_edit'])) {
    header("Location: index.php");
    exit();
}



$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';

$allowed_sorts = ['prioritas', 'created_at', 'tempo'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'created_at';
}

$query = "SELECT * FROM tasks";

if (!empty($search)) {
    $query .= " WHERE nama LIKE :search OR deskripsi LIKE :search";
}

$query .= " ORDER BY $sort DESC";

try {
    $stmt = $pdo->prepare($query);

    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
    }

    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

    <nav class="navbar">
        <h1 class="navbar-title">Manajemen Tugas</h1>
        <div class="navbar-links">
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </nav>

    <div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']); 
            ?>
        </div>
        <?php endif; ?>
        <h2 class="title">Daftar Tugas</h2>
        <img src="orang_laptop 2.jpg" alt="" style="width: 150px; height: 100px; border-radius: 50%;">

        <div class="form-section">
            <h3>Tambah Tugas</h3>
            <form action="" method="POST" class="task-form">
                <label for="nama">Nama Tugas</label>
                <input type="text" id="nama" name="nama" required>
                
                <label for="deskripsi">Deskripsi Tugas</label>
                <input type="text" id="deskripsi" name="deskripsi" required>
                
                <label for="prioritas">Prioritas</label>
                <select name="prioritas" id="prioritas">
                    <option value="0">Penting</option>
                    <option value="1">Sangat Penting</option>
                </select>

                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="0">Belum Selesai</option>
                    <option value="1">Selesai</option>
                </select>

                <label for="tempo">Tanggal</label>
                <input type="date" id="tempo" name="tempo" required>

                <button type="submit" name="tambah_tugas" class="btn btn-add">Tambah</button>
            </form>

            <?php if (!empty($task)): ?>
            <h1>Edit Tugas</h1>
            <form method="POST" action="" class="task-form">
                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">

                <label for="nama">Nama Tugas</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($task['nama']); ?>" required>
                
                <label for="deskripsi">Deskripsi Tugas</label>
                <input type="text" id="deskripsi" name="deskripsi" value="<?php echo htmlspecialchars($task['deskripsi']); ?>" required>

                <label for="prioritas">Prioritas</label>
                <select name="prioritas" id="prioritas">
                    <option value="0" <?php echo $task['prioritas'] == 0 ? 'selected' : ''; ?>>Penting</option>
                    <option value="1" <?php echo $task['prioritas'] == 1 ? 'selected' : ''; ?>>Sangat Penting</option>
                </select>

                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="0" <?php echo $task['status'] == 0 ? 'selected' : ''; ?>>Belum Selesai</option>
                    <option value="1" <?php echo $task['status'] == 1 ? 'selected' : ''; ?>>Selesai</option>
                </select>
                    <label for="tempo">Tanggal</label>
                    <input type="date" id="tempo" name="tempo" 
                    value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($task['tempo']))); ?>" required>
                
                <button type="submit" name="edit_tugas" class="btn btn-add">Simpan</button>
                <a href="index.php?batal_edit=1" class="btn btn-delete">Batal</a>
            </form>
            <?php endif; ?>
        </div>

        <div class="task-list">
            <form method="GET" class="filter-form" style="margin-bottom: 20px; display:flex; gap:10px; align-items:center;">
                <input type="text" name="search" placeholder="Cari nama atau deskripsi..." 
                    value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                    style="padding:5px; border-radius:5px;">

                <select name="sort" style="padding:5px; border-radius:5px;">
                    <option value="created_at" <?php echo ($sort == 'created_at') ? 'selected' : ''; ?>>Tanggal Dibuat</option>
                    <option value="prioritas" <?php echo ($sort == 'prioritas') ? 'selected' : ''; ?>>Prioritas</option>
                    <option value="tempo" <?php echo ($sort == 'tempo') ? 'selected' : ''; ?>>Tempo</option>
                </select>

                <button type="submit" class="btn btn-add">Terapkan</button>
                <a href="index.php" class="btn btn-delete">Reset</a>
            </form>

            <?php if (count($tasks) > 0): ?>
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Prioritas</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td>
                                    <span class="<?php echo $task['status'] == 1 ? 'completed' : ''; ?>">
                                        <?php echo htmlspecialchars($task['nama']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($task['deskripsi']); ?></td>
                                <td><?php echo $task['prioritas'] ? 'Sangat Penting' : 'Penting'; ?></td>
                                <td>
                                    <span class="status <?php echo $task['status'] ? 'done' : 'pending'; ?>">
                                        <?php echo $task['status'] ? 'Completed' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($task['tempo'])); ?></td>
                                <td>
                                    <a href="?update_status=<?php echo $task['id']; ?>" class="btn btn-status">
                                        <?php echo $task['status'] ? 'Batal' : 'Selesai'; ?>
                                    </a>
                                    <a href="?delete_task=<?php echo $task['id']; ?>" 
                                       class="btn btn-delete"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                        Hapus
                                    </a>
                                    <a href="?edit_tugas=<?php echo $task['id']; ?>" class="btn btn-edit">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty-msg">Tidak ada tugas.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
