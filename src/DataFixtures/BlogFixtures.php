<?php

namespace App\DataFixtures;

use App\Entity\Blog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class BlogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Get the category with id 3
        $category = $manager->find(\App\Entity\Category::class, 3);
        
        if (!$category) {
            throw new \Exception('Category with ID 3 does not exist.');
        }

        $now = new DateTimeImmutable();

        for ($i = 1; $i <= 20; $i++) {
            $blog = new Blog();
            $blog->setName('Post ' . $i)
                ->setTitle('Title for Post ' . $i)
                ->setText('This is the content of blog post number ' . $i . '.')
                ->setMetaDesc('Meta description for post ' . $i)
                ->setShortDesc('Short description for post ' . $i)
                ->setImage('thailand_2ea30e37') // You can set an actual image path if you'd like
                ->setCreatedAt($now)
                ->setModifiedAt($now)
                ->setCategory($category);

            $manager->persist($blog);
        }

        $manager->flush();
    }
}
