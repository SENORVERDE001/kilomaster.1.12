<?php
/**
 * Class ConductorController
 * Controller for conductor-related actions.
 */

declare(strict_types=1);

class ConductorController {
    /**
     * @var mysqli Database connection
     */
    private mysqli $conn;

    /**
     * ConductorController constructor.
     * @param mysqli $conn Database connection
     */
    public function __construct(mysqli $conn) {
        $this->conn = $conn;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Validate if current user is a conductor.
     * Redirects to index.php with error if not valid.
     */
    private function validateConductorSession(): void {
        if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'conductor') {
            $_SESSION['error'] = "Acceso denegado. Debes ser un conductor para entrar aquí.";
            header("Location: index.php");
            exit();
        }
    }

    /**
     * Show the dashboard for the conductor.
     *
     * @return void
     */
    public function dashboard(): void {
        $this->validateConductorSession();

        $sql = "SELECT v.id, v.marca, v.modelo, u.nombre AS responsable 
                FROM vehiculos v 
                LEFT JOIN usuarios u ON v.responsable_vehiculo = u.id";

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log("Error preparing query: " . $this->conn->error);
            // Instead of die(), throw an exception or handle error gracefully
            throw new RuntimeException("Error en la consulta de vehículos.");
        }

        if (!$stmt->execute()) {
            error_log("Error executing query: " . $stmt->error);
            throw new RuntimeException("Error en la consulta de vehículos.");
        }

        $result = $stmt->get_result();
        $vehiculos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $result_vehiculos = $result;
        require_once 'views/conductor_dashboard.php';
    }

    /**
     * Close the database connection when the object is destroyed.
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
