CREATE DATABASE IF NOT EXISTS laflamme CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laflamme;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  email VARCHAR(120) UNIQUE,
  password_hash VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120),
  category VARCHAR(60),
  price DECIMAL(10,2),
  image VARCHAR(120)
);

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  res_date DATE,
  res_time TIME,
  full_name VARCHAR(120),
  email VARCHAR(120),
  phone VARCHAR(20),
  table_pref VARCHAR(40),
  guest_count INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE reservation_guests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT,
  guest_name VARCHAR(120),
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);

CREATE TABLE reservation_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT,
  guest_name VARCHAR(120),
  menu_item_id INT,
  quantity INT,
  price DECIMAL(10,2),
  FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

INSERT INTO menu_items(name,category,price,image) VALUES
('Jumbo Shrimp Cocktail','Appetizers',519,'jumbo_shrimp.jpg'),
('Sturia Oscieta Caviar','Appetizers',519,'caviar.jpg'),
('Beef Tartare','Appetizers',519,'beef_tartare.jpg'),
('La Flamme’s Crab Cake','Appetizers',519,'crab_cake.jpg'),
('Lobster Cocktail','Appetizers',519,'lobster_cocktail.jpg'),
('Seafood Platter','Appetizers',1519,'seafood_platter.jpg'),
('Tuna Tartare','Appetizers',519,'tuna_tartare.jpg'),
('Fresh Oysters','Appetizers',519,'oysters.jpg'),

('Signature Ribeye Steak','Butter-Aged Imported Steak Meals',519,'ribeye.jpg'),
('T-Bone Steak','Butter-Aged Imported Steak Meals',449,'tbone.jpg'),
('NY Strip Steak','Butter-Aged Imported Steak Meals',449,'ny_strip.jpg'),
('Flat Iron Steak','Butter-Aged Imported Steak Meals',449,'flat_iron.jpg'),
('Wagyu Cubes','Butter-Aged Imported Steak Meals',1499,'wagyu_cubes.jpg'),

('Ribeye','Frozen Dry-Aged Imported Steaks',1299,'frozen_ribeye.jpg'),
('T-Bone','Frozen Dry-Aged Imported Steaks',1099,'frozen_tbone.jpg'),
('NY Strip','Frozen Dry-Aged Imported Steaks',1199,'frozen_ny_strip.jpg'),
('Flat Iron','Frozen Dry-Aged Imported Steaks',1099,'frozen_flat_iron.jpg'),
('Wagyu Cubes','Frozen Dry-Aged Imported Steaks',1299,'frozen_wagyu.jpg'),

('Soup of the Day','Soup & Salad',519,'soup_day.jpg'),
('French Onion Soup','Soup & Salad',519,'french_onion.jpg'),
('Burrata Salad','Soup & Salad',519,'burrata.jpg'),
('Caesar Salad','Soup & Salad',519,'caesar.jpg'),
('La Flamme’s Salad','Soup & Salad',519,'laflamme_salad.jpg'),
('Classic Wedge Salad','Soup & Salad',519,'wedge_salad.jpg'),

('Steak Rice','Sides',69,'steak_rice.jpg'),
('Plain Rice','Sides',49,'plain_rice.jpg'),
('Mushroom Soup','Sides',149,'mushroom_soup.jpg'),
('Coleslaw','Sides',59,'coleslaw.jpg'),
('Corn and Carrots','Sides',49,'corn_carrots.jpg'),
('Mashed Potato','Sides',19,'mashed_potato.jpg'),
('French Fries','Sides',49,'fries.jpg'),
('Mushroom Sauce','Sides',29,'mushroom_sauce.jpg'),
('Pepper Sauce','Sides',29,'pepper_sauce.jpg'),

('Soda in Cans','Drinks',70,'soda.jpg'),
('Bottled Water','Drinks',50,'water.jpg'),
('Iced Tea','Drinks',170,'iced_tea.jpg');
