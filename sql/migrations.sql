-- Migraciones de Base de Datos - GuiaEmpresarial.pe
-- Fecha: 2026-05-07

-- [4] ÍNDICES FALTANTES EN LA TABLA EMPRESAS
-- Propósito: Optimizar consultas de ordenamiento (vistas) y filtrado (destacadas).

ALTER TABLE empresas ADD INDEX IF NOT EXISTS idx_vistas (vistas);
ALTER TABLE empresas ADD INDEX IF NOT EXISTS idx_destacada (destacada);

-- NOTA: En MySQL < 8.0.19, "IF NOT EXISTS" no es válido para ALTER TABLE.
-- Si falla, usa estas sentencias directas:
-- ALTER TABLE empresas ADD INDEX idx_vistas (vistas);
-- ALTER TABLE empresas ADD INDEX idx_destacada (destacada);
