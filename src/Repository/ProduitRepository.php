<?php

namespace App\Repository;

use App\Entity\Produit;
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

        if ($q !== null && $q !== '') {
            $qb->andWhere('p.nom LIKE :q OR p.description LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($categorieSlug !== null && $categorieSlug !== '') {
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
        return $this->findBy(['enVedette' => true], ['dateCreation' => 'DESC'], $limite);
    }

    /** @return Produit[] */
    public function findNouveautes(int $limite = 4): array
    {
        return $this->findBy([], ['dateCreation' => 'DESC'], $limite);
    }
}