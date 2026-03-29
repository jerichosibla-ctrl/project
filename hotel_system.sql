-- ============================================
-- Online Hotel Reservation System
-- Pagadian City Hotels
-- ============================================

CREATE DATABASE IF NOT EXISTS hotel_system;
USE hotel_system;

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username=admin, password=admin123
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Hotels Table
CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_name VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms Table
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('Available','Unavailable') DEFAULT 'Available',
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Reservations Table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    room_id INT NOT NULL,
    status ENUM('Pending','Confirmed','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- ============================================
-- SEED DATA - Pagadian City Hotels
-- ============================================

INSERT INTO hotels (hotel_name, description, location, image) VALUES
('Citi Hotel Uno', 'A modern and comfortable hotel offering premium amenities in the heart of Pagadian City. Enjoy spacious rooms, free Wi-Fi, and excellent dining options perfect for both business and leisure travelers.', 'Rizal Ave, Pagadian City, Zamboanga del Sur', 'default_hotel.jpg'),
('Raim Hotel and Convention', 'A premier convention hotel ideal for events, seminars, and business meetings. Features elegant function halls, comfortable rooms, and top-notch hospitality services.', 'San Pedro Street, Pagadian City', 'default_hotel.jpg'),
('Mardale Hotel and Convention Center', 'Experience luxury and comfort at Mardale Hotel, one of Pagadian City\'s finest establishments offering exceptional service, beautiful event venues, and well-appointed rooms.', 'Purok Poblacion, Pagadian City', 'default_hotel.jpg'),
('Pagadian Bay Plaza Hotel', 'Overlooking the scenic Pagadian Bay, this hotel offers breathtaking views combined with world-class amenities. Perfect for travelers who want comfort with a view.', 'Baybay, Pagadian City', 'default_hotel.jpg'),
('Chandler Suites', 'Modern suites designed for the discerning traveler. Chandler Suites offers contemporary interiors, high-speed internet, and personalized service for a truly premium stay.', 'Galas, Pagadian City', 'default_hotel.jpg'),
('Hotel Guillermo', 'A well-established hotel in Pagadian City known for its warm hospitality and central location. Ideal for tourists exploring the best of Zamboanga del Sur.', 'Rizal Street, Pagadian City', 'default_hotel.jpg'),
('Executive Travellers Pension House', 'Budget-friendly accommodation without compromising on comfort. Executive Travellers Pension House is perfect for the practical traveler needing clean, safe lodging.', 'Pelaez Street, Pagadian City', 'default_hotel.jpg'),
('GV Hotel Pagadian', 'Part of the trusted GV Hotel chain, offering affordable rooms with reliable amenities. A popular choice for budget-conscious travelers visiting Pagadian City.', 'Rizal Avenue, Pagadian City', 'default_hotel.jpg'),
('RedDoorz @ Pilgrims Hotel', 'A RedDoorz partner property offering standardized quality and affordable prices. Clean, comfortable, and conveniently located in Pagadian City.', 'Quezon Avenue, Pagadian City', 'default_hotel.jpg'),
('FeelHome Suites', 'As the name suggests, FeelHome Suites makes you feel right at home. Cozy rooms, homey atmosphere, and friendly staff make this a favorite among repeat visitors.', 'Veterans Avenue, Pagadian City', 'default_hotel.jpg'),
('Hotel Alindahaw Pagadian City', 'Alindahaw means "cool breeze" — a fitting name for this refreshing retreat in Pagadian City. Offers comfortable rooms, great food, and genuine Southern hospitality.', 'Airport Road, Pagadian City', 'default_hotel.jpg'),
('EP Executive Suites', 'Sophisticated executive suites designed for business travelers and long-term stays. Features fully furnished rooms with kitchen facilities and high-speed internet.', 'Taft Street, Pagadian City', 'default_hotel.jpg'),
('Rotunda Inn Home Stay', 'A charming homestay-style inn located near the Pagadian City Rotunda. Offers a personal touch with clean rooms, home-cooked meals, and local hospitality.', 'Rotunda Area, Pagadian City', 'default_hotel.jpg'),
('Kozy Row Staycation', 'Trendy and Instagram-worthy staycation destination in Pagadian City. Features modern pod-style rooms, social spaces, and fun amenities for a memorable city break.', 'Downstream, Pagadian City', 'default_hotel.jpg');

-- Rooms for each hotel
-- Hotel 1: Citi Hotel Uno
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(1, 'Standard Room', 1500.00, 'A cozy standard room equipped with air conditioning, flat-screen TV, private bathroom, and free Wi-Fi. Suitable for solo travelers or couples.', 'default_room.jpg', 'Available'),
(1, 'Deluxe Room', 2500.00, 'Spacious deluxe room featuring premium bedding, mini-refrigerator, cable TV, work desk, and en-suite bathroom with hot and cold shower.', 'default_room.jpg', 'Available'),
(1, 'Family Room', 3500.00, 'Perfect for families, this room can accommodate up to 4 guests with two double beds, extra amenities, and a comfortable seating area.', 'default_room.jpg', 'Available'),
(1, 'Suite', 5000.00, 'Our premium suite offers a separate living area, king-size bed, jacuzzi, minibar, and panoramic city views for the ultimate luxury experience.', 'default_room.jpg', 'Available');

-- Hotel 2: Raim Hotel
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(2, 'Standard Room', 1200.00, 'Clean and comfortable standard room with essential amenities including AC, TV, private bath, and Wi-Fi access. Perfect for overnight stays.', 'default_room.jpg', 'Available'),
(2, 'Deluxe Room', 2200.00, 'Upgraded deluxe room with superior furnishings, refrigerator, and premium toiletries. Ideal for extended stays.', 'default_room.jpg', 'Available'),
(2, 'Convention Package Room', 3000.00, 'Special room package for convention attendees. Includes room, breakfast, and access to convention amenities.', 'default_room.jpg', 'Available');

-- Hotel 3: Mardale Hotel
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(3, 'Standard Room', 1800.00, 'Comfortable standard room with modern decor, AC, cable TV, and en-suite bathroom. A great value for the quality offered.', 'default_room.jpg', 'Available'),
(3, 'Deluxe Room', 2800.00, 'Elegantly appointed deluxe room featuring plush bedding, work area, mini-bar, and superior bathroom amenities.', 'default_room.jpg', 'Available'),
(3, 'Family Suite', 4500.00, 'Spacious family suite with two bedrooms, a living room, kitchenette, and all modern amenities to make your family stay comfortable.', 'default_room.jpg', 'Available'),
(3, 'Presidential Suite', 8000.00, 'The pinnacle of luxury at Mardale — a full presidential suite with separate living and dining areas, premium furnishings, and VIP services.', 'default_room.jpg', 'Unavailable');

-- Hotel 4: Pagadian Bay Plaza
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(4, 'Bay View Room', 2000.00, 'Wake up to stunning Pagadian Bay views from your room. Features AC, TV, free Wi-Fi, and a private balcony overlooking the water.', 'default_room.jpg', 'Available'),
(4, 'Standard Room', 1500.00, 'Comfortable inland-facing standard room with all essential amenities for a pleasant stay in Pagadian City.', 'default_room.jpg', 'Available'),
(4, 'Deluxe Bay Suite', 4000.00, 'Ultimate bay-facing suite with panoramic windows, premium decor, king-size bed, and exclusive lounge access.', 'default_room.jpg', 'Available');

-- Hotel 5: Chandler Suites
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(5, 'Studio Suite', 2500.00, 'Modern studio suite with open-plan design, fully equipped kitchenette, premium amenities, and high-speed Wi-Fi.', 'default_room.jpg', 'Available'),
(5, 'Junior Suite', 3500.00, 'Elegant junior suite with separate sleeping and living areas, premium furnishings, and business-class amenities.', 'default_room.jpg', 'Available'),
(5, 'Executive Suite', 5500.00, 'Full executive suite perfect for business travelers — features a full kitchen, multiple rooms, and boardroom-ready amenities.', 'default_room.jpg', 'Available');

-- Hotel 6: Hotel Guillermo
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(6, 'Standard Room', 1300.00, 'Welcoming standard room with AC, TV, and private bathroom. Known for warm Guillermo hospitality and comfortable beds.', 'default_room.jpg', 'Available'),
(6, 'Deluxe Room', 2000.00, 'Upgraded room with superior amenities, refrigerator, and improved furnishings for a more comfortable stay.', 'default_room.jpg', 'Available'),
(6, 'Family Room', 3200.00, 'Spacious family room accommodating up to 4 guests with two beds, extra storage, and all essential amenities.', 'default_room.jpg', 'Available');

-- Hotel 7: Executive Travellers
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(7, 'Single Room', 800.00, 'Budget-friendly single room with AC, TV, and shared bathroom access. Clean and safe accommodation for solo travelers.', 'default_room.jpg', 'Available'),
(7, 'Double Room', 1200.00, 'Affordable double room with private bathroom, AC, and TV. Great value for travelers on a budget.', 'default_room.jpg', 'Available'),
(7, 'Standard Room', 1500.00, 'Comfortable standard room with en-suite bathroom and all essential amenities at an unbeatable price.', 'default_room.jpg', 'Available');

-- Hotel 8: GV Hotel
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(8, 'Economy Room', 700.00, 'No-frills economy room offering clean, safe, and comfortable overnight lodging at the most affordable price in the city.', 'default_room.jpg', 'Available'),
(8, 'Standard Room', 1000.00, 'GV Hotel standard room with AC, TV, and private bathroom. Consistent quality you can rely on throughout the GV chain.', 'default_room.jpg', 'Available'),
(8, 'Family Room', 1800.00, 'Larger family room that accommodates up to 4 people — perfect for families traveling on a budget.', 'default_room.jpg', 'Available');

-- Hotel 9: RedDoorz
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(9, 'Standard Room', 990.00, 'RedDoorz standard room with guaranteed cleanliness standards, AC, TV, and free Wi-Fi. Quality assured at budget price.', 'default_room.jpg', 'Available'),
(9, 'Superior Room', 1490.00, 'RedDoorz superior room with extra space, improved amenities, and complimentary breakfast for a better value stay.', 'default_room.jpg', 'Available');

-- Hotel 10: FeelHome Suites
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(10, 'Cozy Room', 1100.00, 'Experience the comforts of home in our cozy room — warm decor, soft bedding, and homey touches that make you feel welcome.', 'default_room.jpg', 'Available'),
(10, 'Suite Room', 2000.00, 'Our suite room offers extra space, a small living area, kitchenette, and all the home comforts you need for a longer stay.', 'default_room.jpg', 'Available'),
(10, 'Family Suite', 3000.00, 'Perfect for families — two bedrooms, a living room, full kitchen, and all amenities for a feel-at-home experience.', 'default_room.jpg', 'Available');

-- Hotel 11: Hotel Alindahaw
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(11, 'Standard Room', 1400.00, 'Cool and comfortable standard room with the famous Alindahaw breeze effect — AC, TV, Wi-Fi, and private bathroom.', 'default_room.jpg', 'Available'),
(11, 'Deluxe Room', 2300.00, 'Spacious deluxe room with garden view, premium amenities, refrigerator, and access to hotel restaurant.', 'default_room.jpg', 'Available'),
(11, 'Family Room', 3500.00, 'Large family room with two queen beds, seating area, and all the amenities needed for a comfortable family vacation.', 'default_room.jpg', 'Available');

-- Hotel 12: EP Executive Suites
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(12, 'Executive Studio', 2000.00, 'Fully furnished executive studio with kitchenette, work desk, high-speed Wi-Fi, and all business essentials.', 'default_room.jpg', 'Available'),
(12, 'One-Bedroom Suite', 3500.00, 'Spacious one-bedroom suite with separate living room, full kitchen, and premium amenities for extended stays.', 'default_room.jpg', 'Available'),
(12, 'Two-Bedroom Suite', 5000.00, 'Ideal for small groups or families — two full bedrooms, a living room, fully equipped kitchen, and laundry access.', 'default_room.jpg', 'Available');

-- Hotel 13: Rotunda Inn
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(13, 'Single Room', 750.00, 'Charming single room with a personal touch — clean, cozy, and conveniently located near the Pagadian City Rotunda.', 'default_room.jpg', 'Available'),
(13, 'Double Room', 1100.00, 'Comfortable double room with home-cooked breakfast option, AC, TV, and genuine local hospitality.', 'default_room.jpg', 'Available'),
(13, 'Family Room', 2000.00, 'Homestyle family room with multiple beds, warm decor, and all the comforts of staying at a local home.', 'default_room.jpg', 'Available');

-- Hotel 14: Kozy Row Staycation
INSERT INTO rooms (hotel_id, room_type, price, description, image, status) VALUES
(14, 'Pod Room', 1200.00, 'Trendy pod-style room perfect for solo travelers or couples. Modern capsule design with smart storage, premium linens, and mood lighting.', 'default_room.jpg', 'Available'),
(14, 'Kozy Studio', 2200.00, 'Stylish studio room with Instagram-worthy decor, smart TV, Bluetooth speaker, high-speed Wi-Fi, and exclusive staycation packages.', 'default_room.jpg', 'Available'),
(14, 'Group Bunk Room', 3500.00, 'Perfect for barkada trips — a bunk-style group room accommodating up to 6 people with shared amenities and social spaces.', 'default_room.jpg', 'Available');
