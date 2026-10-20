<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/" class="text-decoration-none text-zouza">Accueil</a></li>
        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/cart" class="text-decoration-none text-zouza">Mon Panier</a></li>
        <li class="breadcrumb-item active" aria-current="page">Validation de commande</li>
    </ol>
</nav>

<div class="mb-4">
    <h1 class="h3 fw-bold text-dark mb-1">Finaliser ma commande</h1>
    <p class="text-muted small mb-0">Renseignez vos coordonnées pour la préparation et la livraison de vos Zouzas artisanales.</p>
</div>

<?php
$valPrenom        = $old['prenom'] ?? ($userData['prenom'] ?? '');
$valNom           = $old['nom'] ?? ($userData['nom'] ?? '');
$valEmail         = $old['email'] ?? ($userData['email'] ?? '');
$valTelephone     = $old['telephone'] ?? ($userData['telephone'] ?? '');
$valTypeLivraison = $old['type_livraison'] ?? 'livraison';
$valAdresse       = $old['adresse'] ?? '';
$valVille         = $old['ville'] ?? 'Tunis';
$valNotes         = $old['notes'] ?? '';
?>

<div class="row g-4">
    <!-- Formulaire de commande (Gauche) -->
    <div class="col-lg-7">
        <form method="POST" action="<?= BASE_URL ?>/checkout" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <!-- Bloc 1: Coordonnées du destinataire -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
                <h2 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-circle p-2 bg-zouza text-white">1</span>
                    Vos coordonnées
                </h2>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="prenom" class="form-label small fw-semibold">Prénom <span class="text-danger">*</span></label>
                        <input type="text"
                               id="prenom"
                               name="prenom"
                               class="form-control <?= !empty($errors['prenom']) ? 'is-invalid' : '' ?>"
                               value="<?= e($valPrenom) ?>"
                               required
                               maxlength="100">
                        <?php if (!empty($errors['prenom'])): ?>
                            <div class="invalid-feedback"><?= e($errors['prenom']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-6">
                        <label for="nom" class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text"
                               id="nom"
                               name="nom"
                               class="form-control <?= !empty($errors['nom']) ? 'is-invalid' : '' ?>"
                               value="<?= e($valNom) ?>"
                               required
                               maxlength="100">
                        <?php if (!empty($errors['nom'])): ?>
                            <div class="invalid-feedback"><?= e($errors['nom']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-6">
                        <label for="email" class="form-label small fw-semibold">Adresse email <span class="text-danger">*</span></label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                               value="<?= e($valEmail) ?>"
                               required
                               maxlength="191">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                        <?php endif; ?>
                        <div class="form-text small">Votre reçu de commande sera envoyé à cette adresse.</div>
                    </div>

                    <div class="col-sm-6">
                        <label for="telephone" class="form-label small fw-semibold">Numéro de téléphone <span class="text-danger">*</span></label>
                        <input type="tel"
                               id="telephone"
                               name="telephone"
                               class="form-control <?= !empty($errors['telephone']) ? 'is-invalid' : '' ?>"
                               value="<?= e($valTelephone) ?>"
                               placeholder="+216 20 000 000"
                               required
                               maxlength="30">
                        <?php if (!empty($errors['telephone'])): ?>
                            <div class="invalid-feedback"><?= e($errors['telephone']) ?></div>
                        <?php endif; ?>
                        <div class="form-text small">Indispensable pour confirmer la livraison par téléphone.</div>
                    </div>
                </div>
            </div>

            <!-- Bloc 2: Mode de livraison et adresse -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
                <h2 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-circle p-2 bg-zouza text-white">2</span>
                    Mode de réception
                </h2>

                <div class="mb-3">
                    <div class="form-check p-3 border rounded-3 mb-2 <?= $valTypeLivraison === 'livraison' ? 'border-warning bg-light' : '' ?>">
                        <input class="form-check-input"
                               type="radio"
                               name="type_livraison"
                               id="type_livraison_home"
                               value="livraison"
                               <?= $valTypeLivraison === 'livraison' ? 'checked' : '' ?>
                               onchange="toggleDeliveryFields()">
                        <label class="form-check-label fw-semibold text-dark w-100" for="type_livraison_home">
                            &#128666; Livraison à domicile
                            <span class="d-block small fw-normal text-muted">Livraison soignée et sécurisée à l'adresse de votre choix</span>
                        </label>
                    </div>

                    <div class="form-check p-3 border rounded-3 <?= $valTypeLivraison === 'a_emporter' ? 'border-warning bg-light' : '' ?>">
                        <input class="form-check-input"
                               type="radio"
                               name="type_livraison"
                               id="type_livraison_pickup"
                               value="a_emporter"
                               <?= $valTypeLivraison === 'a_emporter' ? 'checked' : '' ?>
                               onchange="toggleDeliveryFields()">
                        <label class="form-check-label fw-semibold text-dark w-100" for="type_livraison_pickup">
                            &#127978; Retrait en atelier (À emporter)
                            <span class="d-block small fw-normal text-muted">Retrait gratuit à notre atelier artisanal</span>
                        </label>
                    </div>
                    <?php if (!empty($errors['type_livraison'])): ?>
                        <div class="text-danger small mt-1"><?= e($errors['type_livraison']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Champs d'adresse conditionnels -->
                <div id="deliveryFieldsGroup">
                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label for="adresse" class="form-label small fw-semibold">Adresse de livraison <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="adresse"
                                   name="adresse"
                                   class="form-control <?= !empty($errors['adresse']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($valAdresse) ?>"
                                   placeholder="Rue, numéro, bâtiment, appartement...">
                            <?php if (!empty($errors['adresse'])): ?>
                                <div class="invalid-feedback"><?= e($errors['adresse']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-4">
                            <label for="ville" class="form-label small fw-semibold">Ville <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="ville"
                                   name="ville"
                                   class="form-control <?= !empty($errors['ville']) ? 'is-invalid' : '' ?>"
                                   value="<?= e($valVille) ?>"
                                   placeholder="Tunis, Ariana, La Marsa...">
                            <?php if (!empty($errors['ville'])): ?>
                                <div class="invalid-feedback"><?= e($errors['ville']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bloc 3: Remarques éventuelles -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 card-zouza">
                <h2 class="h5 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <span class="badge rounded-circle p-2 bg-zouza text-white">3</span>
                    Instructions particulières
                </h2>

                <div>
                    <label for="notes" class="form-label small fw-semibold">Remarques ou précisions (optionnel)</label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              class="form-control <?= !empty($errors['notes']) ? 'is-invalid' : '' ?>"
                              placeholder="Créneau horaire préféré, digicode, consigne pour l'emballage..."
                              maxlength="1000"><?= e($valNotes) ?></textarea>
                    <?php if (!empty($errors['notes'])): ?>
                        <div class="invalid-feedback"><?= e($errors['notes']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bouton de confirmation -->
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-zouza btn-lg fw-bold py-3 rounded-3 shadow-sm">
                    &#10004; Confirmer et enregistrer ma commande (<?= formatPrice($total) ?>)
                </button>
            </div>
        </form>
    </div>

    <!-- Récapitulatif du panier (Droite) -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top card-zouza" style="top: 2rem;">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h3 class="h5 fw-bold text-dark mb-0">Récapitulatif de la commande</h3>
                <a href="<?= BASE_URL ?>/cart" class="btn btn-sm btn-outline-secondary">Modifier</a>
            </div>

            <div class="list-group list-group-flush mb-3">
                <?php foreach ($items as $item): ?>
                    <?php $prod = $item['product']; ?>
                    <div class="list-group-item px-0 py-3 d-flex align-items-center gap-3">
                        <?php if (!empty($prod['image'])): ?>
                            <img src="<?= BASE_URL ?>/uploads/products/<?= e($prod['image']) ?>"
                                 alt="<?= e($prod['nom']) ?>"
                                 class="rounded-3 border"
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted"
                                 style="width: 50px; height: 50px; font-size: 1.3rem;">
                                &#129367;
                            </div>
                        <?php endif; ?>

                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small mb-0"><?= e($prod['nom']) ?></div>
                            <span class="text-muted small">
                                <?= (int)$item['quantity'] ?> &times; <?= formatPrice($prod['prix']) ?>
                            </span>
                        </div>

                        <div class="text-end fw-bold" style="color: #b86b35;">
                            <?= formatPrice($item['subtotal']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="border-top pt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total articles :</span>
                    <span class="fw-semibold"><?= formatPrice($total) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Frais de livraison :</span>
                    <span class="text-success fw-medium small">À régler sur place / offert</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="fs-5 fw-bold text-dark">Total à régler :</span>
                    <span class="fs-4 fw-bold" style="color: #b86b35;">
                        <?= formatPrice($total) ?>
                    </span>
                </div>
            </div>

            <div class="alert alert-light border rounded-3 mt-4 mb-0 small text-muted">
                <div class="d-flex gap-2">
                    <span>&#128176;</span>
                    <div>
                        <strong>Paiement à la livraison / au retrait</strong><br>
                        Le règlement s'effectue en espèces lors de la réception de votre commande.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDeliveryFields() {
    const isPickup = document.getElementById('type_livraison_pickup').checked;
    const fieldsGroup = document.getElementById('deliveryFieldsGroup');
    const adresseInput = document.getElementById('adresse');
    const villeInput = document.getElementById('ville');

    if (isPickup) {
        fieldsGroup.style.display = 'none';
        adresseInput.removeAttribute('required');
        villeInput.removeAttribute('required');
    } else {
        fieldsGroup.style.display = 'block';
        adresseInput.setAttribute('required', 'required');
        villeInput.setAttribute('required', 'required');
    }
}
document.addEventListener('DOMContentLoaded', toggleDeliveryFields);
</script>
