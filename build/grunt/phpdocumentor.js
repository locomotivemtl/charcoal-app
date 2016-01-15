/**
* @file Generate documentation from a PHP source code base.
*/

module.exports = {
    dist: {
        options: {
            config: 'phpdoc.dist.xml',
            directory: ['src/'],
            target : 'build/phpdoc/',
            template : 'responsive-twig'
        }
    }
};
