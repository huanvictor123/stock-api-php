-- ============================================================
-- Seeds — dados de exemplo para desenvolvimento
-- ============================================================

INSERT INTO categories (name, description) VALUES
('Eletronicos',  'Computadores, perifericos e gadgets'),
('Escritorio',   'Material de escritorio e papelaria'),
('Ferramentas',  'Ferramentas manuais e eletricas');

INSERT INTO suppliers (name, email, phone) VALUES
('Tech Distribuidora Ltda',  'contato@techdist.com.br',  '(11) 3000-1000'),
('Office Supply SA',         'vendas@officesupply.com',   '(21) 3000-2000'),
('Ferragem Central',         'pedidos@ferragemcentral.com','(31) 3000-3000');

INSERT INTO products (category_id, supplier_id, name, unit_price, stock_quantity, min_stock) VALUES
(1, 1, 'Teclado Mecanico RGB',    299.90, 15, 5),
(1, 1, 'Mouse Sem Fio',           89.90,  30, 8),
(1, 1, 'Monitor 24 pol Full HD',  899.00,  4, 3),
(2, 2, 'Caderno A4 200 folhas',    18.50, 100,20),
(2, 2, 'Caneta Esferografica cx',  12.90, 200,50),
(3, 3, 'Chave Philips Pro',        35.00,  50,10);

INSERT INTO users (name, email, password) VALUES
('Admin', 'admin@stockapi.com', '$2y$12$placeholderHashAqui');
