<?php
/**
 * Clase ligera para manejar conexión y consultas usando mysqli
 * No usa PDO — cumple con la directiva del usuario.
 */

class MySQLiDatabase
{
    private mysqli $conn;

    public function __construct(string $dbhost, string $dbuser, string $dbpass, string $dbname, string $charset = 'utf8mb4')
    {
        $this->conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($this->conn->connect_errno) {
            die('Error de conexión MySQL: ' . $this->conn->connect_error);
        }
        if (!$this->conn->set_charset($charset)) {
            // no crítico, solo reportar
            error_log('No se pudo establecer charset: ' . $this->conn->error);
        }
    }

    public function getConnection(): mysqli
    {
        return $this->conn;
    }

    /**
     * Ejecuta una consulta preparada con parámetros opcionales
     * Si no se pasan parámetros ejecuta la consulta directa
     * Retorna mysqli_result o boolean
     */
    public function query(string $sql, array $params = [])
    {
        if (empty($params)) {
            return $this->conn->query($sql);
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('MySQL prepare error: ' . $this->conn->error);
        }

        // construir tipos
        $types = '';
        $vals = [];
        foreach ($params as $p) {
            if (is_int($p)) $types .= 'i';
            elseif (is_double($p) || is_float($p)) $types .= 'd';
            else $types .= 's';
            $vals[] = $p;
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$vals);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('MySQL execute error: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        // si es select devolvemos el resultado, si no devolvemos true
        if ($result !== false) {
            return $result;
        }

        return $stmt->affected_rows >= 0;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $res = $this->query($sql, $params);
        if ($res instanceof mysqli_result) {
            return $res->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $res = $this->query($sql, $params);
        if ($res instanceof mysqli_result) {
            return $res->fetch_assoc() ?: null;
        }
        return null;
    }

    public function numRows(string $sql, array $params = []): int
    {
        $res = $this->query($sql, $params);
        if ($res instanceof mysqli_result) {
            return $res->num_rows;
        }
        return 0;
    }

    public function escape(string $value): string
    {
        return $this->conn->real_escape_string($value);
    }

    public function close(): void
    {
        $this->conn->close();
    }
}

?>
