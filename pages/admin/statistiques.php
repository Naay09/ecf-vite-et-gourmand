<section class="contact-form-section">

    <div class="heading">

        <!-- Retour à l'espace administrateur -->
        <a href="/espace-admin" class="d-inline-flex align-items-center gap-2 mb-4 text-decoration-none">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>Retour à l'espace administrateur
        </a>

        <div class="eyebrow">STATISTIQUES</div>

        <h2>Les performances <em>de nos menus.</em></h2>

        <p class="contact-form-note mb-4">Consultez le nombre de commandes et le chiffre d'affaires généré par chaque menu.</p>

        <!-- FILTRES -->
        <form id="form-filtres-statistiques" class="mb-4">

            <div class="row g-3">

                <!-- Filtre par menu -->
                <div class="col-12 col-md-4">

                    <label for="stats-menu" class="form-label">Menu</label>

                    <select id="stats-menu" name="menu_id" class="form-select">
                        <option value="">Tous les menus</option>
                    </select>

                </div>

                <!-- Date de début -->
                <div class="col-12 col-md-3">

                    <label for="stats-date-debut" class="form-label">Date de début</label>

                    <input type="date" id="stats-date-debut" name="date_debut" class="form-control">

                </div>

                <!-- Date de fin -->
                <div class="col-12 col-md-3">

                    <label for="stats-date-fin" class="form-label">Date de fin</label>

                    <input type="date" id="stats-date-fin" name="date_fin" class="form-control">

                </div>
                
                <!-- Boutons des filtres -->
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">

                    <!-- Appliquer les filtres -->
                    <button type="submit" class="btn btn-primary flex-fill">
                        Filtrer
                    </button>

                    <!-- Réinitialiser les filtres -->
                    <button
                        type="button"
                        id="reinitialiser-filtres-statistiques"
                        class="btn btn-outline-secondary"
                        title="Réinitialiser les filtres"
                        aria-label="Réinitialiser les filtres"
                    >
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    </button>

                </div>

            </div>

        </form>

        <!-- MESSAGES -->
        <div
            id="statistiques-feedback"
            class="mb-4"
            role="status"
            aria-live="polite"
        ></div>

        <!-- INDICATEURS -->
        <div class="row g-3 mb-4">

            <div class="col-12 col-md-6">

                <div class="card h-100">

                    <div class="card-body">

                        <h3 class="h6 text-secondary">Nombre de commandes</h3>

                        <p id="stats-total-commandes" class="fs-2 fw-bold mb-0">
                            —
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6">

                <div class="card h-100">

                    <div class="card-body">

                        <h3 class="h6 text-secondary">Chiffre d'affaires</h3>

                        <p id="stats-chiffre-affaires" class="fs-2 fw-bold mb-0" >
                            —
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- GRAPHIQUE DES COMMANDES -->
        <div class="card mb-4">

            <div class="card-body">

                <h3 class="h5 mb-3">Nombre de commandes par menu</h3>

                <div id="stats-graphique-commandes">

                    <p class="text-secondary mb-0">
                        Le graphique sera affiché ici.
                    </p>

                </div>

            </div>

        </div>

        <!-- CHIFFRE D'AFFAIRES PAR MENU -->
        <div class="card">

            <div class="card-body">

                <h3 class="h5 mb-3">Chiffre d'affaires par menu</h3>

                <div class="table-responsive">

                    <table class="table align-middle">

                        <thead>

                            <tr>
                                <th scope="col">Menu</th>
                                <th scope="col">Commandes</th>
                                <th scope="col" class="text-end">Chiffre d'affaires</th>
                            </tr>

                        </thead>

                        <tbody id="stats-tableau-menus">

                            <tr>
                                <td colspan="3" class="text-secondary">
                                    Les statistiques seront affichées ici.
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</section>
