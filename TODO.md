# TODO - Revue Backend vs Base (decoration_shop)

## Étape 1 — Auth/Rôles (bloquant)
- [x] Mettre à jour `backend/auth/login.php` pour récupérer les rôles via `utilisateur_roles` + `roles`
- [x] Mettre à jour `backend/auth/admin.php` pour vérifier correctement le(s) rôle(s) en session

## Étape 2 — Facturation & Stock
- [x] Lire/implémenter `backend/invoices/create.php` conforme à `factures`, `facture_details`, et au trigger `trg_sortie_stock`
- [x] Assurer calcul cohérent des montants HT/TVA/TTC + insertion lignes facture
- [x] (optionnel selon besoin frontend) création `paiements` si statut PAYEE

## Étape 3 — Chiffres / Stock faible
- [ ] Vérifier `backend/dashboard.php` et/ou ajouter endpoint JSON utilisant `vue_chiffre_affaires` et `vue_stock_faible`

## Étape 4 — Durcissement
- [ ] Harmoniser auth sur endpoints (invoices/list, products/list, etc.)
- [ ] (optionnel) Ajouter vérification permissions via `permissions`/`role_permissions`


