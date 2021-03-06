<?php

namespace Majora\Bundle\GeneratorBundle\Tests\Generator\ContentModifier;

use Majora\Bundle\GeneratorBundle\FileInfo\BundleInfo;
use Majora\Bundle\GeneratorBundle\Tests\Stubs\Generator\ContentModifier\AbstractPhpClassContentModifierStub;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Unit test AbstractPhpClassContentModifier
 */
class AbstractPhpClassContentModifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testRetrieveBundleInfoFromGeneratedFile.
     */
    public function retrieveBundleInfoFromGeneratedFileDataProvider()
    {
        return array(

            'file is not a PHP file' => array(
                $this->getSplFileInfoMock(realpath(__DIR__ . '/../../Stubs/RandomTextFileStub.txt')),
                $this->getInflectorMock(),
                null,
                'Majora\Bundle\GeneratorBundle\Generator\Exception\UnsupportedFileForContentModifierException',
            ),

            'file is a bundle class in a bundle not at the root of "src" directory' => array(
                $this->getSplFileInfoMock(realpath(__DIR__ . '/../../Stubs/FileIsBundleNotAtRootSrcStub.php')),
                $this->getInflectorMock(),
                (new BundleInfo())
                    ->setNamespace('SuperVendor\\SuperNamespace\\Bundle\\SuperBundle')
                    ->setClassName('SuperVendorSuperNamespaceSuperBundle')
                ,
            ),

            'file is in a bundle not at the root of "src" directory' => array(
                $this->getSplFileInfoMock(realpath(__DIR__ . '/../../Stubs/FileInBundleNotAtRootSrcStub.php')),
                $this->getInflectorMock(),
                (new BundleInfo())
                    ->setNamespace('SuperVendor\\SuperNamespace\\Bundle\\SuperBundle')
                    ->setClassName('SuperVendorSuperNamespaceSuperBundle')
                ,
            ),

            'file is a bundle class in a bundle at the root of "src" directory' => array(
                $this->getSplFileInfoMock(realpath(__DIR__ . '/../../Stubs/FileIsBundleAtRootSrcStub.php')),
                $this->getInflectorMock(),
                (new BundleInfo())
                    ->setNamespace('SuperBundle')
                    ->setClassName('SuperBundle')
                ,
            ),

            'file is in a bundle at the root of "src" directory' => array(
                $this->getSplFileInfoMock(realpath(__DIR__ . '/../../Stubs/FileInBundleAtRootSrcStub.php')),
                $this->getInflectorMock(),
                (new BundleInfo())
                    ->setNamespace('SuperBundle')
                    ->setClassName('SuperBundle')
                ,
            ),

            'file is not in a bundle' => array(
                $this->getSplFileInfoMock(realpath(__DIR__ . '/../../Stubs/FileNotInBundleStub.php')),
                $this->getInflectorMock(),
                null,
                'UnexpectedValueException',
            ),

        );
    }

    /**
     * Tests retrieveBundleInfoFromGeneratedFile.
     *
     * @test
     * @dataProvider retrieveBundleInfoFromGeneratedFileDataProvider
     */
    public function testRetrieveBundleInfoFromGeneratedFile($generatedFile, $inflector, $expectedBundleInfo, $expectedException = null)
    {
        if(!is_null($expectedException)){
            $this->setExpectedException($expectedException);
        }

        $actualBundleInfo = (new AbstractPhpClassContentModifierStub())->publicRetrieveBundleInfoFromGeneratedFile($generatedFile, $inflector);

        $this->assertEquals($expectedBundleInfo, $actualBundleInfo);
    }

    /**
     * Get mock of Symfony\Component\Finder\SplFileInfo.
     *
     * @param string $filePath
     *
     * @return object
     */
    private function getSplFileInfoMock($filePath)
    {
        return new SplFileInfo($filePath, '', '');
    }

    /**
     * Get mock of Majora\Framework\Inflector\Inflector.
     *
     * @return object
     */
    private function getInflectorMock()
    {
        $inflector = $this->prophesize('Majora\Framework\Inflector\Inflector');

        $inflector->translate('MajoraVendor')->willReturn('SuperVendor');
        $inflector->translate('MajoraNamespace')->willReturn('SuperNamespace');
        $inflector->translate('MajoraEntity')->willReturn('SuperEntity');

        return $inflector->reveal();
    }
}
