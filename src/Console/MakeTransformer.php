<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeTransformer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:transformer 
                            {name : The name of the transformer class}
                            {--model= : The namespace to the model being transformed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new transformer class';

    /**
     * The file system instance.
     *
     * @var string
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param  Filesystem $files
     * @return void
     */
    public function __construct( Filesystem $files )
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->generateTransformer();
    }

    /**
     * Generate the transformer class.
     *
     * @return void
     */
    protected function generateTransformer()
    {
        $name = $this->argument( 'name' );
        $path = app_path() . "/Transformers/$name.php";

        if ( $this->files->exists( $path ) ) {
            return $this->error( "$name already exists!" );
        }

        $this->makeDirectory( $path );
        $this->files->put( $path, $this->makeClass( $name ) );

        $this->info( 'Transformer created successfully.' );
    }

    /**
     * Build a transformers directory if one doesn't exist.
     *
     * @param  string $path
     * @return void
     */
    protected function makeDirectory( string $path )
    {
        if ( ! $this->files->isDirectory( dirname( $path ) ) ) {
            $this->files->makeDirectory( dirname( $path ), 0777, true, true );
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param  string $name
     * @return string
     */
    protected function makeClass( string $name ):string
    {
        $stub = $this->files->get( __DIR__ . '../../resources/stubs/transformer.stub' );

        $stub = $this->replaceNamespace( $stub );
        $stub = $this->replaceClass( $stub, $name );
        $stub = $this->replaceModel( $stub, $name );

        return $stub;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return string
     */
    protected function replaceNamespace( string $stub ):string
    {
        $namespace = $this->laravel->getNamespace() . 'Transformers';

        $stub = str_replace( 'DummyNamespace', $namespace, $stub );

        return $stub;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return string
     */
    protected function replaceClass( string $stub, string $name ):string
    {
        $stub = str_replace( 'DummyClass', $name, $stub );

        return $stub;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return string
     */
    protected function replaceModel( string $stub, string $name ):string
    {
        $model = $this->getModelNamespace( $name );
        $class = $this->getClassFromNamespace( $model );

        $stub = str_replace( 'DummyModelNamespace', $model, $stub );
        $stub = str_replace( 'DummyModelClass', $class, $stub );
        $stub = str_replace( 'DummyModelVariable', camel_case( $class ), $stub );

        return $stub;
    }

    /**
     * Get the class name for the transformer.
     *
     * @param  string $name
     * @return string
     */
    protected function getModelNamespace( string $name ):string
    {
        if ( $this->option( 'model' ) ) {
            return $this->option( 'model' );
        }

        return 'App\\' . str_replace( 'Transformer', '', $name );
    }

    /**
     * Get the class name for the transformer.
     *
     * @param  string $name
     * @return string
     */
    protected function getClassFromNamespace( string $namespace ):string
    {
        return last( explode( '\\', $namespace ) );
    }
}
