<?php
/**
 * Examen Pratique - Bases de Données
 * Université - 2025/2026
 *
 * Déploiement : placez ce fichier dans htdocs/ (XAMPP) ou www/ (WAMP)
 * Base de données utilisée : universite (importer universite.sql)
 *
 * Utilisation : http://localhost/examen.php
 *   Saisir un matricule (ex : 24027) puis cliquer sur Exécuter.
 */

// ----------- Connexion -----------
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'universite';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("Erreur de connexion : " . htmlspecialchars($conn->connect_error));
}
$conn->set_charset('utf8mb4');

// ----------- Matricule (X) -----------
$X = isset($_GET['matricule']) ? trim($_GET['matricule']) : '';

// ----------- Helpers -----------
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function runQuery($conn, $sql, $params = [], $types = '') {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return ['error' => $conn->error, 'rows' => [], 'cols' => []];
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) return ['error' => $stmt->error, 'rows' => [], 'cols' => []];
        $res = $stmt->get_result();
    } else {
        $res = $conn->query($sql);
        if ($res === false) return ['error' => $conn->error, 'rows' => [], 'cols' => []];
        if ($res === true) return ['error' => null, 'rows' => [], 'cols' => [], 'ok' => true];
    }
    $rows = [];
    $cols = [];
    if ($res instanceof mysqli_result) {
        while ($f = $res->fetch_field()) $cols[] = $f->name;
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    return ['error' => null, 'rows' => $rows, 'cols' => $cols];
}

function renderTable($result, $sql) {
    echo '<div class="sql"><div class="sql-label">Requête SQL :</div><pre>' . h($sql) . '</pre></div>';
    if (!empty($result['error'])) {
        echo '<div class="err">Erreur : ' . h($result['error']) . '</div>';
        return;
    }
    if (empty($result['rows'])) {
        echo '<div class="empty">Aucun résultat.</div>';
        return;
    }
    $total = count($result['rows']);
    $rows = array_slice($result['rows'], 0, 2);
    echo '<table><thead><tr>';
    foreach ($result['cols'] as $c) echo '<th>' . h($c) . '</th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($result['cols'] as $c) echo '<td>' . h($r[$c] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<div class="count">Affichage : ' . count($rows) . ' / ' . $total . ' ligne(s)</div>';
}

function question($num, $titre, $conn, $sql, $params = [], $types = '') {
    echo "<section class='q'><h2>Question $num. " . h($titre) . "</h2>";
    $r = runQuery($conn, $sql, $params, $types);
    renderTable($r, $sql);
    echo "</section>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Examen BDD - Université</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; background:#f4f6fa; color:#1a1f2c; margin:0; padding:24px; }
  h1 { color:#2563eb; }
  h2 { color:#0f172a; border-left:4px solid #2563eb; padding-left:10px; margin-top:0; }
  form { background:#fff; padding:16px 20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.08); margin-bottom:20px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
  input[type=text] { padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; }
  button { padding:8px 18px; background:#2563eb; color:#fff; border:0; border-radius:6px; cursor:pointer; font-weight:600; }
  button:hover { background:#1d4ed8; }
  .q { background:#fff; padding:16px 20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.06); margin-bottom:18px; }
  table { width:100%; border-collapse:collapse; margin-top:10px; font-size:14px; }
  th, td { padding:8px 10px; border:1px solid #e2e8f0; text-align:left; }
  th { background:#f1f5f9; }
  tr:nth-child(even) td { background:#fafbfc; }
  .err { color:#b91c1c; background:#fee2e2; padding:8px 12px; border-radius:6px; margin-top:8px; }
  .empty { color:#64748b; font-style:italic; margin-top:8px; }
  .count { color:#64748b; font-size:12px; margin-top:6px; }
  .sql { margin-top:8px; }
  .sql-label { color:#2563eb; font-size:13px; font-weight:600; margin-bottom:4px; }
  .sql pre { background:#0f172a; color:#e2e8f0; padding:12px; border-radius:6px; overflow-x:auto; font-size:12px; white-space:pre-wrap; margin:0; }
  .info { background:#dbeafe; color:#1e40af; padding:10px 14px; border-radius:6px; margin-bottom:16px; }
</style>
</head>
<body>
<h1>Examen Pratique — Bases de Données</h1>
<form method="get">
  <label><strong>Matricule (X) :</strong></label>
  <input type="text" name="matricule" value="<?= h($X) ?>" placeholder="ex : 24027" required>
  <button type="submit">Exécuter les requêtes</button>
</form>

<?php if ($X === ''): ?>
  <div class="info">Saisissez un matricule pour exécuter les questions 3, 5, 6, 7, 8, 9, 11 et 14.</div>
<?php endif; ?>

<?php
// =================== Question 1 ===================
question(1, "Afficher les matières triées par libelle.", $conn,
"SELECT codeMatiere, libelle, credit, semestre
FROM matiere
ORDER BY libelle;");

// =================== Question 2 ===================
question(2, "Matières ayant un crédit >= 4 (triées par libelle).", $conn,
"SELECT codeMatiere, libelle, credit
FROM matiere
WHERE credit >= 4
ORDER BY libelle;");

// =================== Question 3 ===================
if ($X !== '') {
    question(3, "Étudiants triés par nom — recherche du matricule $X.", $conn,
"SELECT matricule, nom, prenom
FROM etudiant
WHERE matricule = ?
ORDER BY nom;", [$X], "s");
} else {
    question(3, "Étudiants triés par nom.", $conn,
"SELECT matricule, nom, prenom FROM etudiant ORDER BY nom LIMIT 2;");
}

// =================== Question 4 ===================
question(4, "Matières contenant le mot 'Base' (triées par libelle).", $conn,
"SELECT codeMatiere, libelle
FROM matiere
WHERE libelle LIKE '%Base%'
ORDER BY libelle;");

// =================== Question 5 ===================
if ($X !== '') {
    question(5, "Moyenne de chaque matière pour le matricule $X.", $conn,
"SELECT n.matricule,
       n.codeMatiere,
       ROUND(n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6, 2) AS moyenne
FROM note n
WHERE n.matricule = ?
ORDER BY n.codeMatiere;", [$X], "s");
}

// =================== Question 6 ===================
if ($X !== '') {
    question(6, "Matières validées par le matricule $X (moyenne >= 10).", $conn,
"SELECT n.matricule,
       n.codeMatiere,
       ROUND(n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6, 2) AS moyenne
FROM note n
WHERE n.matricule = ?
  AND (n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6) >= 10
ORDER BY n.codeMatiere;", [$X], "s");
}

// =================== Question 7 ===================
question(7, "Matières validées par TOUS les étudiants (triées par codeMatiere).", $conn,
"SELECT m.codeMatiere, m.libelle
FROM matiere m
WHERE NOT EXISTS (
    SELECT 1
    FROM note n
    WHERE n.codeMatiere = m.codeMatiere
      AND (n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6) < 10
)
AND EXISTS (SELECT 1 FROM note n2 WHERE n2.codeMatiere = m.codeMatiere)
ORDER BY m.codeMatiere;");

// =================== Question 8 ===================
if ($X !== '') {
    question(8, "Moyenne générale pondérée S1 du matricule $X.", $conn,
"SELECT n.matricule,
       ROUND(
         SUM((n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6) * m.credit)
         / SUM(m.credit), 2
       ) AS moyenne_generale
FROM note n
JOIN matiere m ON m.codeMatiere = n.codeMatiere
WHERE n.matricule = ? AND m.semestre = 'S1'
GROUP BY n.matricule;", [$X], "s");
}

// =================== Question 9 ===================
question(9, "Étudiants ayant une moyenne générale >= 10 (triés par matricule).", $conn,
"SELECT n.matricule,
       ROUND(
         SUM((n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6) * m.credit)
         / SUM(m.credit), 2
       ) AS moyenne_generale
FROM note n
JOIN matiere m ON m.codeMatiere = n.codeMatiere
GROUP BY n.matricule
HAVING moyenne_generale >= 10
ORDER BY n.matricule;");

// =================== Question 10 ===================
question(10, "Étudiant(s) ayant la meilleure moyenne générale.", $conn,
"SELECT n.matricule,
       ROUND(
           SUM(
               (n.devoir * 0.4 +
                GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6
               ) * m.credit
           ) / SUM(m.credit),
           2
       ) AS moyenne_generale
FROM note n
JOIN matiere m
    ON m.codeMatiere = n.codeMatiere
GROUP BY n.matricule
ORDER BY moyenne_generale DESC
LIMIT 1;");

// =================== Question 11 ===================
question(11, "Étudiants ayant validé TOUTES les matières du semestre S1 (triés par matricule).", $conn,
"SELECT DISTINCT n.matricule
FROM note n
WHERE NOT EXISTS (
    SELECT 1
    FROM matiere m
    WHERE m.semestre = 'S1'
      AND NOT EXISTS (
          SELECT 1 FROM note n2
          WHERE n2.matricule = n.matricule
            AND n2.codeMatiere = m.codeMatiere
            AND (n2.devoir * 0.4 + GREATEST(n2.examen, IFNULL(n2.rattrapage, n2.examen)) * 0.6) >= 10
      )
)
ORDER BY n.matricule;");

// =================== Question 12 ===================
question(12, "Matières dont le crédit est supérieur à la moyenne des crédits.", $conn,
"SELECT codeMatiere, libelle, credit
FROM matiere
WHERE credit > (SELECT AVG(credit) FROM matiere)
ORDER BY codeMatiere;");

// =================== Question 13 ===================
$conn->query("DROP VIEW IF EXISTS vue_moyennes");
$create13 = "CREATE VIEW vue_moyennes AS
SELECT n.matricule,
       m.libelle AS matiere,
       ROUND(n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6, 2) AS moyenne,
       CASE WHEN (n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6) >= 10
            THEN 'Validé' ELSE 'Non validé' END AS validation
FROM note n
JOIN matiere m ON m.codeMatiere = n.codeMatiere";
$ok13 = $conn->query($create13);
echo "<section class='q'><h2>Question 13. Créer la vue <code>vue_moyennes</code>.</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>" . h($create13) . "</pre></div>";
echo $ok13 ? "<div class='count'>Vue créée avec succès.</div>" : "<div class='err'>" . h($conn->error) . "</div>";
echo "</section>";

// =================== Question 14 ===================
if ($X !== '') {
    question(14, "Données de la vue vue_moyennes pour le matricule $X.", $conn,
"SELECT * FROM vue_moyennes WHERE matricule = ? ORDER BY matricule;", [$X], "s");
} else {
    question(14, "Données de la vue vue_moyennes (toutes).", $conn,
"SELECT * FROM vue_moyennes ORDER BY matricule LIMIT 2;");
}

// =================== Question 15 ===================
$conn->query("DROP VIEW IF EXISTS vue_moyenne_semestre");
$create15 = "CREATE VIEW vue_moyenne_semestre AS
SELECT m.semestre,
       n.matricule,
       ROUND(
         SUM((n.devoir * 0.4 + GREATEST(n.examen, IFNULL(n.rattrapage, n.examen)) * 0.6) * m.credit)
         / SUM(m.credit), 2
       ) AS moyenne_generale
FROM note n
JOIN matiere m ON m.codeMatiere = n.codeMatiere
GROUP BY m.semestre, n.matricule";
$ok15 = $conn->query($create15);
echo "<section class='q'><h2>Question 15. Vue moyenne générale par semestre.</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>" . h($create15) . "</pre></div>";
if ($ok15) {
    $r15 = runQuery($conn, "SELECT * FROM vue_moyenne_semestre ORDER BY semestre, matricule LIMIT 2;");
    renderTable($r15, "SELECT * FROM vue_moyenne_semestre ORDER BY semestre, matricule;");
} else {
    echo "<div class='err'>" . h($conn->error) . "</div>";
}
echo "</section>";

// =================== Question 16 ===================
$conn->query("DROP VIEW IF EXISTS vue_moyenne_validee");
$create16 = "CREATE VIEW vue_moyenne_validee AS
SELECT * FROM vue_moyenne_semestre
WHERE moyenne_generale >= 10
WITH CHECK OPTION";
$ok16 = $conn->query($create16);
echo "<section class='q'><h2>Question 16. Vue avec CHECK OPTION (moyenne >= 10).</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>" . h($create16) . "</pre></div>";
if ($ok16) {
    $r16 = runQuery($conn, "SELECT * FROM vue_moyenne_validee ORDER BY semestre, matricule LIMIT 2;");
    renderTable($r16, "SELECT * FROM vue_moyenne_validee;");
} else {
    echo "<div class='err'>" . h($conn->error) . "</div>";
}
echo "</section>";

// =================== Question 17-20 (commandes admin) ===================
echo "<section class='q'><h2>Question 17. Créer l'utilisateur SQL <code>professeur</code>.</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>"
   . h("CREATE USER 'professeur'@'localhost' IDENTIFIED BY 'motdepasse';\nSELECT User FROM mysql.user WHERE User='professeur';")
   . "</pre></div>";
echo "<div class='count'>À exécuter en tant que root dans phpMyAdmin (nécessite droits admin).</div>";
echo "</section>";

echo "<section class='q'><h2>Question 18. Accorder SELECT, INSERT, UPDATE, DELETE sur <code>note</code>.</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>"
   . h("GRANT SELECT, INSERT, UPDATE, DELETE ON universite.note TO 'professeur'@'localhost';\nSHOW GRANTS FOR 'professeur'@'localhost';")
   . "</pre></div></section>";

echo "<section class='q'><h2>Question 19. Retirer le droit DELETE.</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>"
   . h("REVOKE DELETE ON universite.note FROM 'professeur'@'localhost';\nSHOW GRANTS FOR 'professeur'@'localhost';")
   . "</pre></div></section>";

echo "<section class='q'><h2>Question 20. Retirer UPDATE sauf sur la colonne note (devoir/examen/rattrapage).</h2>";
echo "<div class='sql'><div class='sql-label'>Requête SQL :</div><pre>"
   . h("REVOKE UPDATE ON universite.note FROM 'professeur'@'localhost';\n"
     . "GRANT UPDATE (devoir, examen, rattrapage) ON universite.note TO 'professeur'@'localhost';\n"
     . "SHOW GRANTS FOR 'professeur'@'localhost';")
   . "</pre></div></section>";

$conn->close();
?>
</body>
</html>
