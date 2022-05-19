<?php  
	 // Vérifiez si le paramètre id est spécifié dans l'URL.  
	 if (isset($_GET['id'])) {  
	     // Pour éviter toute injection SQL, préparez l'instruction et exécutez-la.  
	     $stmt = $pdo->prepare('SELECT * FROM produits WHERE id = ?');  
	     $stmt->execute([$_GET['id']]);  
	     /*  Récupérer le produit de la base de données et retourner le résultat sous forme de tableau.*/  
	     $produit = $stmt->fetch(PDO::FETCH_ASSOC);  
	     /* Vérifiez si le produit existe (le tableau n'est pas vide)*/  
	     if (!$produit) {  
	         /*Erreur simple à afficher si l'id du produit n'existe pas (le tableau est vide)*/  
	         exit('le produit n\'existe pas!');  
	     }
	 } else {  
	     //  Erreur simple à afficher si l'id n'a pas été spécifié.  
	     exit('le produit n\'existe pas!');  
	 }
	 ?>
	

	<?=template_header('Produit')?>
	<div class="produit content-wrapper">
	    <img src="imgs/<?=$produit['img']?>" width="500" height="500" alt="<?=$produit['nom']?>">
	    <div>
	        <h1 class="name"><?=$produit['nom']?></h1>
	        <span class="price"> &dollar;<?=$produit['prix']?>
	              <?php if ($produit['prix_Réel'] > 0): ?>
	             <span class="prix_Réel"> &dollar;<?=$produit['prix_Réel']?></span>
	             <?php endif; ?>
	         </span>
	         <form action="index.php?page=panier" method="post">
	             <input type="number" name="quantité" value="1" min="1" max="<?=$produit['quantité']?>" placeholder="Quantité" required>
	             <input type="hidden" name="produit_id" value="<?=$produit['id']?>">            <input type="submit" value="Ajouter au panier">
	         </form>
	         <div class="description">
	             <?=$produit['description']?>
	         </div>
	     </div></div>
	 <?=template_footer()?>
	
