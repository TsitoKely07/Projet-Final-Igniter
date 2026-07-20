# V2 Migration TODO

## Step 1: Database Migration
- [x] Apply V2 schema to existing database

## Step 2: Operator Side
- [x] Update OperateurController for prefixes with operator association
- [x] Update prefixes.php view with operator selection
- [x] Create commission management (controller method + view)
- [x] Update gains display to split by own operator vs inter-operator
- [x] Create decompte operateur (controller method + view)

## Step 3: Client Side
- [x] Update BaseClientController::getFrais() for operator-specific fees
- [x] Update OperationController::transfert() for inter-operator detection
- [x] Handle "no withdrawal fees for other operators"
- [x] Update transfertMultiple similarly

## Step 4: Routes
- [x] Add new routes: /operator/commissions, /operator/decompte

## Step 5: Layout
- [x] Update operator sidebar with new links

