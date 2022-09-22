<?php
namespace AuditBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;
use DataDog\PagerBundle\Pagination;
use DataDog\AuditBundle\Entity\AuditLog;

use Carbon\Carbon;

use AbsenceBundle\Entity\Absence;

class AuditController extends Controller
{
    use DoctrineControllerTrait;
    public function filters(QueryBuilder $qb, $key, $val)
    {
        switch ($key) {
        case 'history':
            if ($val) {
                $orx = $qb->expr()->orX();
                $orx->add('s.fk = :fk');
                $orx->add('t.fk = :fk');
                $qb->andWhere($orx);
                $qb->setParameter('fk', intval($val));
            }
            break;
        case 'class':
            $orx = $qb->expr()->orX();
            $orx->add('s.class = :class');
            $orx->add('t.class = :class');
            $qb->andWhere($orx);
            $qb->setParameter('class', $val);
            break;
        case 'blamed':
            if ($val === 'null') {
                $qb->andWhere($qb->expr()->isNull('a.blame'));
            } else {
                // this allows us to safely ignore empty values
                // otherwise if $qb is not changed, it would add where the string is empty statement.
                $qb->andWhere($qb->expr()->eq('b.fk', ':blame'));
                $qb->setParameter('blame', $val);
            }
            break;
        default:
            // if user attemps to filter by other fields, we restrict it
            throw new \Exception("filter not allowed");
        }
    }
    /**
     * @Method("GET")
     * @Template
     * @Route("/audit", name="audit")
     */
    public function indexAction(Request $request)
    {
        $lastweek = Carbon::now()->subDays(7)->format('Y-m-d');

        Pagination::$defaults = array_merge(Pagination::$defaults, [
            'limit' => 50,
            'range' => 10,
        ]);

        $qb = $this->repo("DataDogAuditBundle:AuditLog")
            ->createQueryBuilder('a')
            ->addSelect('s', 't', 'b')
            ->innerJoin('a.source', 's')
            ->leftJoin('a.target', 't')
            ->leftJoin('a.blame', 'b')
            ->where("DATE_FORMAT(a.loggedAt, '%Y-%m-%d') > '{$lastweek}'");
        $options = [
            'sorters' => ['a.loggedAt' => 'DESC'],
            'applyFilter' => [$this, 'filters'],
        ];

        $logs = new Pagination($qb, $request, $options);

        return compact('logs');
    }
    /**
     * @Route("/audit/diff/{id}", name="app_audit_diff")
     * @Method("GET")
     * @Template
     */
    public function diffAction(AuditLog $log)
    {
        return compact('log');
    }
}
