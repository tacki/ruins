<?php
use Doctrine\ORM\Event\LifecycleEventArgs,
    Layer\Money;

class LayerEventManager
{
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof \Entities\Character) {
            var_dump("postLoad");
            var_dump($eventArgs->getEntity()->money);
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        if ($eventArgs->getEntity() instanceof \Entities\Character) {
            var_dump("preUpdate");

            foreach($eventArgs->getEntity() as $bla => $blubb) {
                var_dump($bla, $blubb);
            }

            var_dump($eventArgs->getOldValue('money'));
            var_dump($eventArgs->getNewValue('money'));

        }
    }
}
?>