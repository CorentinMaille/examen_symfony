<?php

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 *
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function save(Cart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Cart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Permet de récupérer le panier "en cours" de l'utilisateur connecté
     */
    public function getCurrentUserActiveCart($userId){
        $query = "SELECT cart.id as cart_id, cart_content.id as cart_content_id, cart_content.*, product.* FROM cart 
        INNER JOIN cart_content ON cart.id = cart_content.cart_id 
        INNER JOIN product ON cart_content.product_id = product.id
        WHERE user_id = ? AND cart.status = false";
        $conn = $this->getEntityManager()->getConnection();
        $preparedQuery = $conn->prepare($query);
        $results = $preparedQuery->executeQuery([$userId]);
        return $results->fetchAllAssociative();
    }

    /**
     * Permet de récupérer tous les paniers "en cours" des uutilisateurs du site E-Commerce
     */
    public function getAllActiveCart(){
        $query = "SELECT user.lastname, user.firstname, user.id as user_id, cart_content.*, product.* FROM cart 
        INNER JOIN cart_content ON cart.id = cart_content.cart_id 
        INNER JOIN product ON cart_content.product_id = product.id
        INNER JOIN user ON cart.user_id = user.id
        WHERE cart.status = false
        ORDER BY user_id";
        $conn = $this->getEntityManager()->getConnection();
        $preparedQuery = $conn->prepare($query);
        $results = $preparedQuery->executeQuery([]);
        return $results->fetchAllAssociative();
    }

//    /**
//     * @return Cart[] Returns an array of Cart objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Cart
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
