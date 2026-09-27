<?php
/**
 * ZOUZA SHOP - Modèle de Base
 *
 * Fournit l'accès à la connexion PDO partagée pour tous les modèles métiers.
 * Ne contient aucun code HTML ni logique de présentation.
 */

require_once __DIR__ . '/../config/database.php';

abstract class Model
{
    /**
     * Instance PDO partagée
     */
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
}
