<!-- ESPACE EMPLOYÉ -->

<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">ESPACE PROFESSIONNEL</div>

        <h2>Tableau de <em>bord.</em></h2>

        <p class="contact-form-note mb-4">
            Retrouvez les commandes à traiter et accédez aux différentes fonctionnalités de gestion.</p>

        <!-- INDICATEURS -->

        <div class="row g-4">

            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6">Commandes en attente</h3>
                        <p class="fs-2 fw-semibold mb-0" id="employe-commandes-attente">
                            —
                        </p>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6">Commandes en préparation</h3>
                        <p class="fs-2 fw-semibold mb-0" id="employe-commandes-preparation">
                            —
                        </p>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6">Avis à valider</h3>
                        <p class="fs-2 fw-semibold mb-0" id="employe-avis-attente">
                            —
                        </p>

                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<!-- GESTION DES COMMANDES -->

<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">GESTION DES COMMANDES</div>

        <h2>Les commandes <em>clients.</em></h2>

        <p class="contact-form-note mb-4"> Consultez et gérez les commandes reçues par Vite & Gourmand.</p>

        <!-- FILTRES DES COMMANDES -->

    <div id="employe-commandes-filtres" class="mb-4">

        <div class="row g-3">

            <!-- Filtre par statut -->
            <div class="col-12 col-md-5">

                <label for="employe-filtre-statut" class="form-label">
                    Statut de la commande
                </label>

                <select
                    id="employe-filtre-statut"
                    class="form-select"
                >
                    <option value="tous">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="accepte">Acceptée</option>
                    <option value="en_preparation">En préparation</option>
                    <option value="en_cours_de_livraison">En cours de livraison</option>
                    <option value="livre">Livrée</option>
                    <option value="en_attente_retour_materiel">En attente du retour de matériel</option>
                    <option value="terminee">Terminée</option>
                    <option value="annulee">Annulée</option>
                </select>

            </div>

            <!-- Recherche par client ou commande -->
            <div class="col-12 col-md-7">

                <label for="employe-recherche-commande" class="form-label">
                    Rechercher une commande
                </label>

                <input
                    type="search"
                    id="employe-recherche-commande"
                    class="form-control"
                    placeholder="Nom, prénom, e-mail ou numéro"
                >

            </div>

        </div>

        <!-- Nombre de résultats -->
        <p
            id="employe-filtres-resultat"
            class="form-text mt-3"
            role="status"
            aria-live="polite"
        ></p>

    </div>

        <!-- MESSAGES -->

        <div id="employe-commandes-feedback" role="status" aria-live="polite">

        </div>

        <!-- LISTE DES COMMANDES -->

        <div id="employe-commandes-liste" class="row g-4">

        </div>

    </div>

</section>

<!-- AUTRES FONCTIONNALITÉS -->

<section class="contact-form-section">

    <div class="heading">

        <div class="eyebrow">ADMINISTRATION QUOTIDIENNE</div>

        <h2>Gérer <em>l'établissement.</em></h2>

        <div class="row g-4">

            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Menus et plats</h3>
                        <p>Modifier et gérer les menus proposés.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Horaires</h3>
                        <p>Mettre à jour les horaires d'ouverture.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h5">Avis clients</h3>
                        <p>Valider ou refuser les avis reçus.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<!-- MODALE DÉTAIL COMMANDE EMPLOYÉ -->

<div
    class="modal fade"
    id="modal-commande-employe"
    tabindex="-1"
    aria-labelledby="modal-commande-employe-titre"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <!-- EN-TÊTE -->

            <div class="modal-header">

                <h2
                    class="modal-title fs-4"
                    id="modal-commande-employe-titre"
                >
                    Détail de la commande
                </h2>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fermer"
                ></button>

            </div>

            <!-- CONTENU -->

            <div class="modal-body">

                <div
                    id="employe-detail-feedback"
                    role="status"
                    aria-live="polite"
                ></div>

                <div id="employe-detail-contenu" class="d-none">

                    <!-- CLIENT -->

                    <h3 class="h5 mb-3">Informations du client</h3>

                    <p>
                        <strong>Client :</strong>
                        <span id="employe-detail-client"></span>
                    </p>

                    <p>
                        <strong>E-mail :</strong>
                        <span id="employe-detail-email"></span>
                    </p>

                    <p>
                        <strong>Téléphone :</strong>
                        <span id="employe-detail-telephone"></span>
                    </p>

                    <hr class="my-4">

                    <!-- PRESTATION -->

                    <h3 class="h5 mb-3">Prestation</h3>

                    <p>
                        <strong>Menu :</strong>
                        <span id="employe-detail-menu"></span>
                    </p>

                    <p>
                        <strong>Date :</strong>
                        <span id="employe-detail-date"></span>
                    </p>

                    <p>
                        <strong>Heure :</strong>
                        <span id="employe-detail-heure"></span>
                    </p>

                    <p>
                        <strong>Convives :</strong>
                        <span id="employe-detail-convives"></span>
                    </p>

                    <p>
                        <strong>Adresse :</strong>
                        <span id="employe-detail-adresse"></span>
                    </p>

                    <p>
                        <strong>Statut :</strong>
                        <span id="employe-detail-statut"></span>
                    </p>

                    <hr class="my-4">

                    <!-- PRIX -->

                    <h3 class="h5 mb-3">Détail du prix</h3>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Prix du menu</span>
                        <span id="employe-detail-prix-menu"></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Remise</span>
                        <span id="employe-detail-remise"></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Frais de livraison</span>
                        <span id="employe-detail-livraison"></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong id="employe-detail-total"></strong>
                    </div>

                    <hr class="my-4">

                    <!-- HISTORIQUE -->

                    <h3 class="h5 mb-3">Historique de la commande</h3>

                    <ul
                        id="employe-detail-historique"
                        class="list-group list-group-flush"
                    ></ul>

                </div>

            </div>

            <!-- ACTIONS -->

            <div class="modal-footer">

                <!-- Réservé aux prochaines US -->
                <div
                    id="employe-detail-actions"
                    class="d-flex flex-wrap gap-2"
                ></div>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Fermer
                </button>

            </div>

        </div>

    </div>
</div>
