-- Cập nhật password admin thành hash MD5
-- Password '123' được hash thành MD5: 202cb962ac59075b964b07152d234b70

UPDATE `admin` SET `password` = '202cb962ac59075b964b07152d234b70' WHERE `email` = 'tqhien614@gmail.com';
UPDATE `admin` SET `password` = '202cb962ac59075b964b07152d234b70' WHERE `email` = 'tienduc@gmail.com';

-- Hoặc cập nhật tất cả admin có password là '123'
-- UPDATE `admin` SET `password` = MD5('123') WHERE `password` = '123';

