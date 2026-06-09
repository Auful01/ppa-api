<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');

// Check if table already exists
$exists = $pdo->query("SHOW TABLES LIKE 'pengalihan_assets'")->rowCount() > 0;
if ($exists) {
    echo "Table pengalihan_assets already exists.\n";
    exit(0);
}

$sql = "CREATE TABLE `pengalihan_assets` (
    `id` char(36) NOT NULL,
    `id_inventory` varchar(255) DEFAULT NULL,
    `id_inv_prev` char(36) DEFAULT NULL,
    `nrp_user_prev` varchar(255) DEFAULT NULL,
    `nrp_user_new` varchar(255) DEFAULT NULL,
    `inv_number_next` varchar(255) DEFAULT NULL,
    `tanggal_pengalihan` date DEFAULT NULL,
    `foto_pengalihan` text DEFAULT NULL,
    `remark` text DEFAULT NULL,
    `device` varchar(50) DEFAULT NULL,
    `site` varchar(50) DEFAULT NULL,
    `dept` varchar(255) DEFAULT NULL,
    `dept_prev` varchar(255) DEFAULT NULL,
    `spek` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    `deleted_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);
echo "Table pengalihan_assets created successfully.\n";

// Also add spek to PengalihanAsset fillable (done separately in model)
echo "Remember to add 'spek' to PengalihanAsset \$fillable if needed.\n";
