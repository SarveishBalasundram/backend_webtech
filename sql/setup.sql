-- Create the database (if not already created)
CREATE DATABASE IF NOT EXISTS assetManagement;
USE assetManagement;

-- Create category table
CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Create asset table with foreign key to category
CREATE TABLE IF NOT EXISTS asset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    department VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL,
    purchase_date DATE NOT NULL,
    warranty_expiry DATE NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    usage_type VARCHAR(20) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES category(id)
);

-- Insert categories (if not already inserted)
INSERT IGNORE INTO category (name) VALUES 
('Laptops'),
('Desktop Computers'),
('Printers'),
('Monitors / Screens'),
('Mobile Devices'),
('External Storage Devices');

-- Insert original 25 assets plus 30 new ones (55 total)
INSERT INTO asset (id, name, category_id, department, status, purchase_date, warranty_expiry, value, usage_type) VALUES
-- Original 25 assets (Laptops 1-4, Desktops 5-8, Printers 9-12, Monitors 13-16, Mobile 17-20, Storage 21-25)
(1, 'Dell XPS 13', 1, 'IT', 'In Use', '2022-06-15', '2025-06-15', 5000.00, 'Personal'),
(2, 'HP Pavilion 15', 1, 'Finance', 'In Use', '2021-06-11', '2024-06-11', 4300.00, 'Personal'),
(3, 'Lenovo ThinkPad X1', 1, 'Executive', 'In Use', '2022-05-20', '2025-05-20', 7000.00, 'Critical'),
(4, 'Asus ROG Zephyrus', 1, 'Design', 'Storage', '2023-06-10', '2026-06-10', 9000.00, 'Backup'),
(5, 'Dell OptiPlex 7090', 2, 'Admin', 'In Use', '2021-02-15', '2024-02-15', 4500.00, 'Shared'),
(6, 'HP EliteDesk 800', 2, 'HR', 'In Use', '2022-09-10', '2025-09-10', 4800.00, 'Personal'),
(7, 'Apple iMac 24"', 2, 'Design', 'In Use', '2023-04-05', '2026-04-05', 8200.00, 'Critical'),
(8, 'Lenovo IdeaCentre', 2, 'Finance', 'Storage', '2020-12-20', '2023-12-20', 4100.00, 'Backup'),
(9, 'HP LaserJet Pro', 3, 'Admin', 'Under Repair', '2021-04-10', '2024-04-10', 1500.00, 'Shared'),
(10, 'Canon Pixma G3010', 3, 'Finance', 'In Use', '2022-09-25', '2025-09-25', 1000.00, 'Shared'),
(11, 'Brother HL-L2350DW', 3, 'HR', 'Storage', '2020-05-15', '2023-05-15', 900.00, 'Backup'),
(12, 'Epson EcoTank L3150', 3, 'IT', 'In Use', '2023-01-15', '2026-01-15', 1300.00, 'Critical'),
(13, 'Samsung Monitor 27"', 4, 'Design', 'In Use', '2022-11-12', '2025-11-12', 1200.00, 'Personal'),
(14, 'Dell Ultrasharp 24"', 4, 'Design', 'In Use', '2021-08-05', '2024-08-05', 1100.00, 'Shared'),
(15, 'LG Ultrawide 34"', 4, 'Design', 'In Use', '2023-04-05', '2026-04-05', 2000.00, 'Critical'),
(16, 'BenQ GW2480', 4, 'Admin', 'In Use', '2022-02-12', '2025-02-12', 950.00, 'Backup'),
(17, 'iPhone 13', 5, 'Executive', 'In Use', '2023-02-10', '2026-02-10', 4200.00, 'Personal'),
(18, 'Samsung Galaxy Tab S7', 5, 'Sales', 'In Use', '2022-07-15', '2025-07-15', 2800.00, 'Shared'),
(19, 'iPad Air', 5, 'Marketing', 'In Use', '2023-01-05', '2026-01-05', 3000.00, 'Personal'),
(20, 'Google Pixel 7', 5, 'IT', 'In Use', '2023-03-20', '2026-03-20', 3500.00, 'Testing'),
(21, 'WD My Passport 2TB', 6, 'IT', 'In Use', '2022-10-10', '2025-10-10', 400.00, 'Backup'),
(22, 'Seagate Backup Plus 4TB', 6, 'Marketing', 'In Use', '2023-01-20', '2026-01-20', 600.00, 'Backup'),
(23, 'LaCie Rugged 5TB', 6, 'IT', 'In Use', '2021-11-25', '2024-11-25', 850.00, 'Critical'),
(24, 'SanDisk Extreme 1TB', 6, 'Finance', 'In Use', '2022-08-18', '2025-08-18', 500.00, 'Testing'),
(25, 'Crucial X8 2TB', 6, 'Executive', 'Storage', '2023-05-12', '2026-05-12', 700.00, 'Backup'),

-- New additional 30 assets
-- Laptops (26-32)
(26, 'MacBook Pro 14"', 1, 'Executive', 'In Use', '2023-07-10', '2026-07-10', 9500.00, 'Critical'),
(27, 'Microsoft Surface Laptop 5', 1, 'Marketing', 'In Use', '2022-12-05', '2025-12-05', 6500.00, 'Personal'),
(28, 'Acer Swift 3', 1, 'Sales', 'In Use', '2023-02-18', '2026-02-18', 3800.00, 'Shared'),
(29, 'Razer Blade 15', 1, 'Design', 'In Use', '2023-05-22', '2026-05-22', 8500.00, 'Critical'),
(30, 'LG Gram 17', 1, 'Executive', 'In Use', '2023-01-15', '2026-01-15', 7200.00, 'Personal'),
(31, 'MSI Creator Z16', 1, 'Design', 'In Use', '2022-11-30', '2025-11-30', 8800.00, 'Critical'),
(32, 'Framework Laptop', 1, 'IT', 'Under Repair', '2023-03-05', '2026-03-05', 5500.00, 'Testing'),

-- Desktop Computers (33-39)
(33, 'Apple Mac Studio', 2, 'Design', 'In Use', '2023-04-20', '2026-04-20', 12000.00, 'Critical'),
(34, 'HP Z2 Mini G9', 2, 'IT', 'In Use', '2022-10-15', '2025-10-15', 6500.00, 'Shared'),
(35, 'Dell Precision 3660', 2, 'Engineering', 'In Use', '2023-01-10', '2026-01-10', 7800.00, 'Critical'),
(36, 'Lenovo ThinkStation P360', 2, 'Design', 'In Use', '2022-09-05', '2025-09-05', 7200.00, 'Critical'),
(37, 'Intel NUC 12 Extreme', 2, 'IT', 'Storage', '2023-02-28', '2026-02-28', 5800.00, 'Backup'),
(38, 'ASUS ProArt Station', 2, 'Marketing', 'In Use', '2022-12-12', '2025-12-12', 6800.00, 'Shared'),
(39, 'Microsoft Surface Studio 2', 2, 'Design', 'In Use', '2023-03-15', '2026-03-15', 9500.00, 'Critical'),

-- Printers (40-45)
(40, 'HP OfficeJet Pro 9025e', 3, 'Admin', 'In Use', '2023-01-20', '2026-01-20', 1200.00, 'Shared'),
(41, 'Brother MFC-L8900CDW', 3, 'Finance', 'In Use', '2022-11-15', '2025-11-15', 1800.00, 'Shared'),
(42, 'Canon imageCLASS MF753Cdw', 3, 'HR', 'In Use', '2023-02-10', '2026-02-10', 1500.00, 'Shared'),
(43, 'Epson WorkForce Pro WF-3820', 3, 'Sales', 'In Use', '2022-10-05', '2025-10-05', 1100.00, 'Shared'),
(44, 'Xerox VersaLink C405', 3, 'Marketing', 'Storage', '2021-12-15', '2024-12-15', 2200.00, 'Backup'),
(45, 'Lexmark CX725de', 3, 'IT', 'Under Repair', '2022-09-20', '2025-09-20', 2500.00, 'Testing'),

-- Monitors/Screens (46-51)
(46, 'Samsung Odyssey G7 32"', 4, 'Design', 'In Use', '2023-03-10', '2026-03-10', 2800.00, 'Critical'),
(47, 'Dell 49" Curved Monitor', 4, 'Engineering', 'In Use', '2022-12-18', '2025-12-18', 3500.00, 'Critical'),
(48, 'LG 27UN850-W 4K', 4, 'Design', 'In Use', '2023-01-25', '2026-01-25', 1800.00, 'Personal'),
(49, 'ASUS ProArt PA32UCX', 4, 'Design', 'In Use', '2023-04-05', '2026-04-05', 4200.00, 'Critical'),
(50, 'Acer Predator X38', 4, 'IT', 'In Use', '2022-11-30', '2025-11-30', 3200.00, 'Testing'),
(51, 'Apple Pro Display XDR', 4, 'Executive', 'In Use', '2023-02-15', '2026-02-15', 5000.00, 'Critical'),

-- Mobile Devices (52-55)
(52, 'iPhone 14 Pro', 5, 'Executive', 'In Use', '2023-05-20', '2026-05-20', 4800.00, 'Personal'),
(53, 'Samsung Galaxy S23 Ultra', 5, 'Sales', 'In Use', '2023-03-15', '2026-03-15', 4500.00, 'Personal'),
(54, 'iPad Pro 12.9"', 5, 'Marketing', 'In Use', '2023-04-10', '2026-04-10', 3800.00, 'Critical'),
(55, 'Microsoft Surface Duo 2', 5, 'IT', 'Under Repair', '2022-12-05', '2025-12-05', 3200.00, 'Testing');