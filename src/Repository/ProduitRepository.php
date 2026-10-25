<?php

namespace App\Repository;

use App\Entity\Produit;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Catalogue : recherche par mot-clé, filtre par catégorie et tri.
     *
     * @return Produit[]
     */
    public function rechercher(?string $q, ?string $categorieSlug, string $tri = 'recent'): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.categorie', 'c');

        $qb->andWhere('p.statut = :publie')
           ->setParameter('publie', Produit::STATUT_PUBLIE);

        if (null !== $q && '' !== $q) {
            $qb->andWhere('p.nom LIKE :q OR p.description LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        if (null !== $categorieSlug && '' !== $categorieSlug) {
            $qb->andWhere('c.slug = :slug')
               ->setParameter('slug', $categorieSlug);
        }

        match ($tri) {
            'prix_asc' => $qb->orderBy('p.prix', 'ASC'),
            'prix_desc' => $qb->orderBy('p.prix', 'DESC'),
            'nom' => $qb->orderBy('p.nom', 'ASC'),
            default => $qb->orderBy('p.dateCreation', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }

    /** @return Produit[] */
    public function findEnVedette(int $limite = 4): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.categorie', 'c')
            ->andWhere('p.statut = :publie')
            ->setParameter('publie', Produit::STATUT_PUBLIE)
            ->andWhere('p.enVedette = :vedette')
            ->setParameter('vedette', true)
            ->andWhere('c.slug != :proto')
            ->setParameter('proto', 'prototypes')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $exclureIds identifiants de produits à ne pas renvoyer
     *
     * @return Produit[]
     */
    public function findNouveautes(int $limite = 4, array $exclureIds = []): array
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.categorie', 'c')
            ->andWhere('p.statut = :publie')
            ->setParameter('publie', Produit::STATUT_PUBLIE)
            ->andWhere('c.slug != :proto')
            ->setParameter('proto', 'prototypes');

        if ([] !== $exclureIds) {
            $qb->andWhere('p.id NOT IN (:ids)')
               ->setParameter('ids', $exclureIds);
        }

        return $qb
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    public function findPrototypes(int $limite = 4): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.categorie', 'c')
            ->andWhere('p.statut = :publie')
            ->setParameter('publie', Produit::STATUT_PUBLIE)
            ->andWhere('c.slug = :proto')
            ->setParameter('proto', 'prototypes')
            ->orderBy('p.dateCreation', 'DESC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }

    /** @return Produit[] */
    public function findByVendeur(Utilisateur $vendeur): array
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->join('p.categorie', 'c')
            ->andWhere('p.vendeur = :vendeur')
            ->setParameter('vendeur', $vendeur)
            ->orderBy('p.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
