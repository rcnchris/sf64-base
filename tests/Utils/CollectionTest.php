<?php

namespace App\Tests\Utils;

use App\Tests\AppTestCase;
use App\Utils\Collection;

final class CollectionTest extends AppTestCase
{

    /**
     * Intances avec différents formats
     */

    public function testInstanceWithoutParam(): void
    {
        $c = new Collection();
        self::assertInstanceOf(Collection::class, $c);
        self::assertIsArray($c->getCollectionOptions());
        self::assertSame(',', $c->getCollectionOptions('separator'));
        self::assertEmpty($c->getCollectionTitle());
        self::assertTrue($c->isEmpty());
    }

    public function testInstanceWithOnlyTitle(): void
    {
        $c = new Collection(title: __FUNCTION__);
        self::assertInstanceOf(Collection::class, $c);
        self::assertSame(__FUNCTION__, $c->getCollectionTitle());
        self::assertTrue($c->isEmpty());
    }

    public function testSetCollectionTitle(): void
    {
        $c = new Collection();
        self::assertInstanceOf(Collection::class, $c->setCollectionTitle(__FUNCTION__));
        self::assertSame(__FUNCTION__, $c->getCollectionTitle());
        self::assertTrue($c->isEmpty());
    }

    public function testInstanceWithCollectionList(): void
    {
        $c = new Collection(['ola', 'ole', 'oli', 'oly']);
        self::assertInstanceOf(Collection::class, $c);
        self::assertIsArray($c->toArray());
        self::assertTrue($c->isList());
        self::assertFalse($c->isAssociative());
        self::assertFalse($c->isEmpty());
        self::assertCount(4, $c);
    }

    public function testInstanceWithCollectionAssociative(): void
    {
        $c = new Collection([
            'firstname' => 'Mathis',
            'age' => 17
        ]);
        self::assertIsArray($c->toArray());
        self::assertTrue($c->isAssociative());
        self::assertFalse($c->isList());
        self::assertFalse($c->isEmpty());
        self::assertCount(2, $c);
    }

    public function testInstanceWithArrayListInStringWithSeparator(): void
    {
        $c = new Collection('ola, ole, oli, oly');
        self::assertInstanceOf(Collection::class, $c);
        self::assertTrue($c->isList(), 'Le tableau n\'est pas une liste');
        self::assertFalse($c->isAssociative(), 'Le tableau est associatif');
        self::assertFalse($c->isEmpty(), 'La liste est vide !');
        self::assertCount(4, $c, 'Le compte est incorrect');
    }

    public function testInstanceWithArrayListInStringWithWrongSeparatorReturnException(): void
    {
        $this->expectException(\LogicException::class);
        new Collection('ola;ole;oli;oly');
    }

    public function testInstanceWithStringFilename(): void
    {
        $c = new Collection($this->getRootDir('/composer.json'));
        self::assertTrue($c->isAssociative());
        self::assertArrayHasKey('name', $c->toArray());
    }

    public function testInstanceWithJsonString(): void
    {
        $json = file_get_contents($this->getRootDir('/composer.json'));
        $c = new Collection($json);
        self::assertTrue($c->isAssociative());
    }

    public function testInstanceWithJsonStringWithSetDataFromJson(): void
    {
        $json = file_get_contents($this->getRootDir('/composer.json'));
        $c = new Collection();
        $c->setDataFromJson($json);
        self::assertTrue($c->isAssociative());
    }

    public function testInstanceWithWrongJsonStringWithSetDataFromJson(): void
    {
        $json = file_get_contents($this->getRootDir('/tests/files/wrong.json'));
        $c = new Collection();
        $this->expectException(\Exception::class);
        $c->setDataFromJson($json);
    }

    public function testInstanceWithWrongJsonFileReturnException(): void
    {
        $this->expectException(\Exception::class);
        new Collection($this->getRootDir('/tests/files/wrong.json'));
    }

    public function testInstanceWithXmlFile(): void
    {
        $c = new Collection($this->getRootDir('/phpunit.xml.dist'));
        self::assertTrue($c->isAssociative());
    }

    public function testInstanceWithNotImplementedMimeFile(): void
    {
        $this->expectException(\Exception::class);
        new Collection($this->getRootDir('/tests/files/file.pdf'));
    }

    public function testInstanceWithInteger(): void
    {
        $c = new Collection(1234);
        self::assertInstanceOf(Collection::class, $c);
        self::assertCount(1, $c);
        self::assertEquals(1234, $c->get(0));
    }

    public function testInstanceWithDouble(): void
    {
        $c = new Collection(1234.41);
        self::assertInstanceOf(Collection::class, $c);
        self::assertCount(1, $c);
        self::assertEquals(1234.41, $c->get(0));
    }

    public function testInstanceWithDateTimeInterfaceObject(): void
    {
        $c = new Collection($this->getFaker()->dateTimeThisCentury());
        self::assertInstanceOf(Collection::class, $c);
        self::assertCount(9, $c);
        self::assertArrayHasKeys($c->toArray(), ['y', 'm', 'd', 'h', 'i', 's', 'ts', 'tz', 'offset']);
        self::assertInstanceOf(\DateTimeZone::class, $c->tz);

        $o = new \stdClass();
        $o->firstname = 'Jobi';
        $o->lastname = 'Joba';
    }

    public function testInstanceWithStdClassObject(): void
    {
        $o = new \stdClass();
        $o->firstname = 'Jobi';
        $o->lastname = 'Joba';

        $c = new Collection($o);
        self::assertInstanceOf(Collection::class, $c);
        self::assertArrayHasKeys($c->toArray(), ['firstname', 'lastname']);
        self::assertSame('Jobi', $c->firstname);
        self::assertSame('Joba', $c->lastname);
    }

    public function testInstanceWithSimpleXmlObject(): void
    {
        $filename = $this->getRootDir('/phpunit.xml.dist');
        $o = new \SimpleXMLElement(file_get_contents($filename));
        $c = new Collection($o, __FUNCTION__);
        self::assertInstanceOf(Collection::class, $c);
        self::assertFalse($c->isEmpty());
        self::assertArrayHasKeys($c->toArray(), ['@attributes', 'php', 'coverage']);
    }

    public function testInstanceWithNotImplementedTypeReturnLogicException(): void
    {
        $this->expectException(\LogicException::class);
        new Collection(true);
    }

    public function testInstanceWithNotImplementedObjectReturnLogicException(): void
    {
        $this->expectException(\Exception::class);
        new Collection(new \DateTimeZone('Europe/Paris'));
    }

    /**
     * GETTERS
     */

    public function testGetWithListCollection(): void
    {
        $c = new Collection(['ola', 'ole', 'oli']);
        self::assertSame('ole', $c->get(1));
    }

    public function testGetWithAssociativeCollection(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'age' => 17
        ]);
        self::assertSame(17, $c->get('age'));
    }

    public function testGetWithNodeAssociativeCollection(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'notes' => [
                'maths' => 12,
                'sport' => 17,
            ],
        ]);
        self::assertSame(12, $c->get('notes.maths'));
        self::assertSame(12, $c->notes->maths);
        self::assertInstanceOf(Collection::class, $c->notes);
    }

    public function testGetValues(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);
        $values = $c->getValues();
        self::assertInstanceOf(Collection::class, $values);
        self::assertTrue($values->isList());
        self::assertCount(2, $c);
        self::assertContains('Jobi', $values);
        self::assertContains('Joba', $values);
    }

    public function testJoin(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);
        self::assertSame('Jobi,Joba', $c->join());
    }

    public function testJoinWithSeparator(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);
        self::assertSame('Jobi;Joba', $c->join(';'));
    }

    public function testExtract(): void
    {
        $c = new Collection([
            [
                'firstname' => 'Jobi',
                'lastname' => 'Joba'
            ],
            [
                'firstname' => 'Test',
                'lastname' => 'Aillons'
            ]
        ]);
        self::assertEquals(['Jobi', 'Test'], $c->extract('firstname')->toArray());
        self::assertEquals([
            'Joba' => 'Jobi',
            'Aillons' => 'Test'
        ], $c->extract('firstname', 'lastname')->toArray());
    }

    public function testFlip(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba'
        ]);
        self::assertSame([
            'Jobi' => 'firstname',
            'Joba' => 'lastname',
        ], $c->flip()->toArray());
    }

    public function testSort(): void
    {
        $c = new Collection([15, 4, 25]);
        self::assertSame([4, 15, 25], $c->sort()->toArray());
        self::assertSame([4, 15, 25], $c->sort('FAKE')->toArray());
        self::assertSame([25, 15, 4], $c->sort('DESC')->toArray());
    }

    public function testDistinct(): void
    {
        $c = new Collection(['ola', 'ole', 'ola', 'oli', 'olI']);
        self::assertEquals([
            0 => 'ola',
            1 => 'ole',
            3 => 'oli',
            4 => 'olI'
        ], $c->distinct()->toArray());
    }

    public function testDiff(): void
    {
        $c1 = new Collection(['ola', 'ole', 'ola', 'oli']);
        $c2 = new Collection(['ole', 'ola', 'oly', 'olI']);
        self::assertEquals([3 => 'oli'], $c1->diff($c2)->toArray());
        self::assertEquals([2 => 'oly', 3 => 'olI'], $c2->diff($c1)->toArray());
    }

    public function testMap(): void
    {
        $c = new Collection([1, 2, 3]);
        self::assertSame([2, 4, 6], $c->map(fn($item) => $item * 2)->toArray());

        $c = new Collection('ola,ole,oli');
        self::assertSame(['Ola', 'Ole', 'Oli'], $c->map(fn($item) => ucfirst($item))->toArray());
    }

    public function testFilter(): void
    {
        $c = new Collection([1, 2, 3, 4]);
        self::assertSame(
            [
                2 => 3,
                3 => 4
            ],
            $c->filter(fn($v) => $v > 2)->toArray()
        );

        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);
        self::assertSame(
            ['firstname' => 'Jobi'],
            $c->filter(fn($v, $k) => $k === 'firstname')->toArray()
        );

        $c = new Collection([
            [
                'firstname' => 'Jobi',
                'lastname' => 'Joba',
            ],
            [
                'firstname' => 'Test',
                'lastname' => 'Aillons',
            ]
        ]);
        self::assertSame(
            [
                1 => [
                    "firstname" => "Test",
                    "lastname" => "Aillons"
                ]
            ],
            $c->filter(fn($item) => $item['firstname'] === 'Test')->toArray()
        );
    }

    public function testSearch(): void
    {
        $c = new Collection([1, 2, 3, 4]);
        self::assertSame(1, $c->search(2));

        $c = new Collection([1, 2, 3, 4]);
        self::assertFalse($c->search(5));

        $c = new Collection('ola,ole,oli');
        self::assertEquals(1, $c->search('ole'));
    }

    public function testSlice(): void
    {
        $c = new Collection([1, 2, 3, 4]);
        self::assertSame([3, 4], $c->slice(2)->toArray());
        self::assertSame([2, 3], $c->slice(1, 2)->toArray());
        self::assertSame([2 => 3, 3 => 4], $c->slice(2, null, true)->toArray());
    }

    public function testMerge(): void
    {
        $c = new Collection([
            'option1' => true,
            'option2' => 25,
        ]);
        self::assertSame(
            [
                'option1' => true,
                'option2' => 30,
                'option3' => 'ola',
            ],
            $c->merge(['option2' => 30, 'option3' => 'ola'])->toArray()
        );
    }

    public function testKeysWithListCollection(): void
    {
        $c = new Collection(['ola', 'ole', 'oli']);
        $keys = $c->keys();
        self::assertInstanceOf(Collection::class, $keys);
        self::assertEquals([0, 1, 2], $keys->toArray());
    }

    public function testHasKeyWithListCollection(): void
    {
        $c = new Collection(['ola', 'ole', 'oli', 'oly']);
        self::assertTrue($c->hasKey(1));
        self::assertFalse($c->hasKey('oli'));
    }

    public function testHasValueWithListCollection(): void
    {
        $c = new Collection(['ola', 'ole', 'oli', 'oly']);
        self::assertTrue($c->hasValue('oli'));
        self::assertFalse($c->hasValue('fake'));
    }

    public function testKeysWithAssociativeCollection(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'age' => 17
        ]);
        $keys = $c->keys();
        self::assertInstanceOf(Collection::class, $keys);
        self::assertEquals(['firstname', 'age'], $keys->toArray());
    }

    public function testHasKeyWithAssociativeCollection(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'age' => 17
        ]);
        self::assertTrue($c->hasKey('firstname'));
        self::assertFalse($c->hasKey('Jobi'));
    }

    public function testHasValueWithAssociativeCollection(): void
    {
        $c = new Collection([
            'firstname' => 'Mathis',
            'age' => 17
        ]);
        self::assertTrue($c->hasValue('Mathis'));
        self::assertFalse($c->hasValue('age'));
    }

    public function testFirst(): void
    {
        $c = new Collection([12, 10, 5]);
        self::assertEquals(12, $c->first());

        $c = new Collection();
        self::assertNull($c->first());
    }

    public function testLast(): void
    {
        $c = new Collection([12, 10, 5]);
        self::assertEquals(5, $c->last());

        $c = new Collection();
        self::assertNull($c->last());
    }

    public function testRand(): void
    {
        $c = new Collection([12, 10, 5]);
        self::assertContains($c->rand(), $c);
    }

    /**
     * Opérations
     */

    public function testSumWithList(): void
    {
        $c = new Collection([12, 10, 5]);
        self::assertEquals(27, $c->sum());
    }

    public function testSumWithAssociative(): void
    {
        $c = new Collection([
            'notes' => [
                'maths' => 12,
                'sport' => 17,
            ],
        ]);
        self::assertEquals(29, $c->notes->sum());
    }

    public function testAverageWithList(): void
    {
        $c = new Collection([12, 10, 5]);
        self::assertEquals(9, $c->average());
    }

    public function testAverageWithAssociative(): void
    {
        $c = new Collection([
            'notes' => [
                'maths' => 12,
                'sport' => 17,
            ],
        ]);
        self::assertEquals(14.5, $c->notes->average());
    }

    /**
     * METHODES TO*
     */

    public function testToArray(): void
    {
        $c = new Collection('ola, ole, oli');
        self::assertIsArray($c->toArray());
    }

    public function testToJson(): void
    {
        $c = new Collection('ola, ole, oli, oly');
        self::assertInstanceOf(Collection::class, $c);
        self::assertCount(4, $c);
        self::assertIsString($c->toJson());
    }

    public function testToXmlWithListCollection(): void
    {
        $c = new Collection(['Jobi,Joba']);
        self::assertInstanceOf(Collection::class, $c);
        self::assertSame(
            "<?xml version=\"1.0\"?>\n<root><item><0>Jobi,Joba</0></item></root>\n",
            $c->toXml(),
            "La chaîne XML est mal formée"
        );
    }

    public function testToXmlWithAssociativeCollection(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);
        self::assertInstanceOf(Collection::class, $c);
        self::assertSame(
            "<?xml version=\"1.0\"?>\n<root><item><firstname>Jobi</firstname></item><item><lastname>Joba</lastname></item></root>\n",
            $c->toXml(),
            "La chaîne XML est mal formée"
        );
    }

    public function testToHttpQuery(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);
        self::assertInstanceOf(Collection::class, $c);
        self::assertSame('firstname=Jobi&lastname=Joba', $c->toHttpQuery());
    }

    public function testToHtmlAttributes(): void
    {
        $c = new Collection([
            'id' => 'Jobi',
            'class' => 'btn btn-primary',
            'required' => true,
        ]);
        self::assertInstanceOf(Collection::class, $c);
        self::assertSame('id="Jobi" class="btn btn-primary" required', $c->toHtmlAttributes());
    }

    public function testToFile(): void
    {
        $filename = $this->getRootDir('/tests/files/testToFile.json');
        $c = new Collection('ola, ole, oli');
        self::assertInstanceOf(\SplFileInfo::class, $c->toFile($filename));
        self::assertFileExists($filename);
    }

    public function testSet(): void
    {
        $c = new Collection([
            'firstname' => 'Jobi',
            'lastname' => 'Joba',
        ]);

        self::assertCount(2, $c);
        $c->set('firstname', 'Truc');
        self::assertCount(2, $c);
        self::assertSame('Truc', $c->get('firstname'));

        $c->set('company', 'MRCC');
        self::assertCount(3, $c);
        self::assertSame('MRCC', $c->get('company'));

        self::assertFalse($c->set());
        self::assertCount(3, $c);

        self::assertTrue($c->set(null, 'Ola'));
        self::assertCount(4, $c);
        self::assertSame('Ola', $c->get(0));
    }

    public function testGetArrayType(): void
    {
        self::assertFalse(Collection::getArrayType([]));

        $a = ['ola', 'ole', 'oli'];
        self::assertSame('list', Collection::getArrayType($a));
        self::assertSame('list', (new Collection($a))->getType());

        $a = [
            'ola' => 'oli',
            'ole' => 'olu'
        ];
        self::assertSame('asso', Collection::getArrayType($a));
        self::assertSame('asso', (new Collection($a))->getType());

        $a = [
            [
                'firstname' => 'Jobi',
                'lastname' => 'Joba'
            ],
            [
                'firstname' => 'Test',
                'lastname' => 'Aillons'
            ],
        ];
        self::assertSame('multi', Collection::getArrayType($a));
        self::assertSame('multi', (new Collection($a))->getType());
    }

    /**
     * Méthodes magiques
     */

    public function testMagicToStringReturnSameToJson(): void
    {
        $c = new Collection('ola,ole,oli,oly');
        self::assertInstanceOf(Collection::class, $c);
        self::assertSame($c->toJson(), (string)$c);
    }

    public function testMagicGet(): void
    {
        $c = new Collection([
            'firstname' => 'Mathis',
            'age' => 17
        ]);
        self::assertInstanceOf(Collection::class, $c);
        self::assertEquals(17, $c->age);
    }

    public function testMagicCall(): void
    {
        $c = new Collection([
            'firstname' => 'Mathis',
            'age' => 17
        ]);
        self::assertInstanceOf(Collection::class, $c);
        self::assertEquals(17, $c->age());
    }

    /**
     * Classes implémentées
     */

    public function testArrayAccessMethods(): void
    {
        $faker = $this->getFaker();
        $data = [
            'firstname' => $faker->firstName(),
            'lastname' => $faker->lastName(),
            'birthday' => $faker->dateTimeThisCentury(),
        ];
        $c = new Collection($data);

        // offsetExists
        self::assertTrue(isset($c['firstname']));
        self::assertTrue(isset($c['lastname']));
        self::assertTrue(isset($c['birthday']));
        self::assertFalse(isset($c['fake']));

        // offsetGet
        self::assertSame($data['firstname'], $c['firstname']);
        self::assertSame($data['lastname'], $c['lastname']);
        self::assertSame($data['birthday'], $c['birthday']);
        self::assertNull($c['fake']);

        // offsetSet Existant
        $c['firstname'] = 'Jobi';
        self::assertTrue(isset($c['firstname']));
        self::assertSame('Jobi', $c['firstname']);
        self::assertCount(3, $c);

        // offsetSet Ajout
        $company = $faker->company();
        $c['company'] = $company;
        self::assertCount(4, $c);
        self::assertTrue(isset($c['company']));
        self::assertSame($company, $c['company']);

        // offsetUnset
        unset($c['firstname']);
        self::assertCount(3, $c);
        self::assertFalse(isset($c['firstname']));
    }

    public function testIterator(): void
    {
        $faker = $this->getFaker();
        $data = [
            'firstname' => $faker->firstName(),
            'lastname' => $faker->lastName(),
        ];
        $c = new Collection($data);

        foreach ($c as $key => $value) {
            self::assertTrue(in_array($key, array_keys($data)));
            self::assertTrue(in_array($value, array_values($data)));
        }
    }
}
