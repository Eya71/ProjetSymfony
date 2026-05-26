<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $client1 = new Client();
        $client1->setUsername('amira');
        $client1->setEmail('amira@email.com');
        $client1->setAdresse('Nabeul');
        $client1->setNumTel('55667788');
        $client1->setIdPhoto('files_profil/amira.jpg');
        $client1->setPwd('123456');
        $manager->persist($client1);

        $client2 = new Client();
        $client2->setUsername('eyaabbes');
        $client2->setEmail('eya.abbes@insat.ucar.tn');
        $client2->setAdresse('Sfax route Menzel Chaker km 3.5');
        $client2->setNumTel('29204518');
        $client2->setIdPhoto('files_profil/eya.jpg');
        $client2->setPwd('eyaabbes01');
        $manager->persist($client2);

        $client3 = new Client();
        $client3->setUsername('skander');
        $client3->setEmail('skander@email.com');
        $client3->setAdresse('Tunis centre');
        $client3->setNumTel('22334455');
        $client3->setIdPhoto('files_profil/skander.jpg');
        $client3->setPwd('123456');
        $manager->persist($client3);

        $manager->flush();
    }
}