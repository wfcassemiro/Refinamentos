-- Script SQL para adicionar a coluna lecture_time à tabela upcoming_announcements
-- Execute este script no banco de dados se quiser ter o campo de horário da palestra

ALTER TABLE `upcoming_announcements` 
ADD COLUMN `lecture_time` TIME DEFAULT '19:00:00' 
AFTER `announcement_date`;

-- Comentário: 
-- Esta coluna armazenará o horário da palestra (ex: 19:00:00 para 19h)
-- O valor padrão é 19:00:00 (7 PM)
-- Após adicionar esta coluna, você pode usar o arquivo manage_announcements.php original