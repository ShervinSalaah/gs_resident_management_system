<?php
session_start();

// Protect page - redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

require_once 'resident.class.php';
$resident = new Resident();

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $resident->delete($_GET['id']);
    header("Location: residents.php?msg=deleted");
    exit();
}

// Handle Search
$searchTerm = $_GET['search'] ?? '';
$residents = $searchTerm ? $resident->search($searchTerm) : $resident->getAll();

$message = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added':
            $message = '<div class="alert success">✅ Resident added successfully!</div>';
            break;
        case 'updated':
            $message = '<div class="alert success">✅ Resident updated successfully!</div>';
            break;
        case 'deleted':
            $message = '<div class="alert success">✅ Resident deleted successfully!</div>';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents Management - Grama Niladhari</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }
        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:hover {
            background: #f8f9fa;
            cursor: pointer;
        }

        .clickable-row {
            transition: background 0.2s;
        }

        .clickable-row:hover {
            background: #e8f0fe !important;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }
        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-row {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    (style generated with AI)
    <div class="header">
        <div class="header-content">
            <h1>🏠 Residents Management</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php echo $message; ?>

        <div class="search-bar">
            <form method="GET" action="residents.php" class="search-form">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Search by Name, NIC, Address, Phone, or Email..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="btn btn-primary">🔍 Search</button>
                <?php if ($searchTerm): ?>
                    <a href="residents.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" onclick="openAddModal()">➕ Add New Resident</button>
            </form>
        </div>

        <div class="table-container">
            <?php if (count($residents) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>NIC</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($residents as $r): ?>
                        <tr class="clickable-row">
            
                            <td onclick="viewResident(<?php echo $r['id']; ?>)">
                                <?php echo htmlspecialchars($r['full_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($r['nic']); ?></td>
                            <td><?php echo htmlspecialchars(substr($r['address'], 0, 50)) . '...'; ?></td>
                            <td><?php echo htmlspecialchars($r['phone']); ?></td>
                            <td class="actions">
                                <button class="btn btn-info btn-sm" onclick="viewResident(<?php echo $r['id']; ?>)">
                                    👁️ View
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(<?php echo $r['id']; ?>)">
                                    ✏️ Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteResident(<?php echo $r['id']; ?>)">
                                    🗑️ Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <h3>No residents found</h3>
                    <p><?php echo $searchTerm ? 'No matches for "' . htmlspecialchars($searchTerm) . '"' : 'Start by adding a new resident!'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="residentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Resident</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="residentForm">
                <input type="hidden" name="id" id="residentId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" id="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="dob" id="dob" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>NIC Number *</label>
                        <input type="text" name="nic" id="nic" required maxlength="12">
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="text" name="phone" id="phone" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Address *</label>
                    <textarea name="address" id="address" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label>Occupation</label>
                        <input type="text" name="occupation" id="occupation">
                    </div>
                </div>

                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" id="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">💾 Save Resident</button>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Resident Details</h2>
                <span class="close" onclick="closeViewModal()">&times;</span>
            </div>
            <div id="residentDetails"></div>
        </div>
    </div>

    <script>
        // Open Add Modal
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Resident';
            document.getElementById('residentForm').reset();
            document.getElementById('residentId').value = '';
            document.getElementById('residentModal').style.display = 'block';
        }

        // Open Edit Modal
        function openEditModal(id) {
            fetch('resident_actions.php?action=get&id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = 'Edit Resident';
                    document.getElementById('residentId').value = data.id;
                    document.getElementById('full_name').value = data.full_name;
                    document.getElementById('dob').value = data.dob;
                    document.getElementById('nic').value = data.nic;
                    document.getElementById('address').value = data.address;
                    document.getElementById('phone').value = data.phone;
                    document.getElementById('email').value = data.email;
                    document.getElementById('occupation').value = data.occupation || '';
                    document.getElementById('gender').value = data.gender;
                    document.getElementById('residentModal').style.display = 'block';
                });
        }

        // View Resident
        function viewResident(id) {
            fetch('resident_actions.php?action=get&id=' + id)
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <div class="detail-row">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value">${data.full_name}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value">${data.dob}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">NIC</div>
                            <div class="detail-value">${data.nic}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Address</div>
                            <div class="detail-value">${data.address}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value">${data.phone}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email</div>
                            <div class="detail-value">${data.email}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Occupation</div>
                            <div class="detail-value">${data.occupation || 'N/A'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value">${data.gender}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Registered Date</div>
                            <div class="detail-value">${data.registered_date}</div>
                        </div>
                    `;
                    document.getElementById('residentDetails').innerHTML = html;
                    document.getElementById('viewModal').style.display = 'block';
                });
        }

        // Delete Resident
        function deleteResident(id) {
            if (confirm('Are you sure you want to delete this resident? This action cannot be undone.')) {
                window.location.href = `resident_actions.php?action=delete&id=${id}`;
            }
        }

        // Close Modals
        function closeModal() {
            document.getElementById('residentModal').style.display = 'none';
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Handle form submission
        document.getElementById('residentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = document.getElementById('residentId').value;
            
            fetch('resident_actions.php?action=' + (id ? 'update' : 'add'), {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'residents.php?msg=' + (id ? 'updated' : 'added');
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        });
    </script>
</body>
</html>