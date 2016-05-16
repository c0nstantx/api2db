<?php
/**
 * (c) Konstantine Christofilos <kostas.christofilos@gmail.com>
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Thanks :)
 */

namespace Model;
use StanfordNLP\NERTagger;

/**
 * Description of NERTagger3
 *
 * @author Konstantine Christofilos <kostas.christofilos@gmail.com>
 */
class NERTagger3 extends NERTagger
{
    protected $libFolder;
    
    public function __construct($classifier, $jar, $libFolder, $java_options = array('-mx300m'))
    {
        parent::__construct($classifier, $jar, $java_options);
        $this->libFolder = $libFolder;
    }
    
    public function getLibFolder()
    {
        return $this->libFolder;
    }

    /**
     * Tag multiple arrays of tokens for sentences
     *
     * @param $sentences array array of arrays of tokens
     *
     * @return mixed
     */
    public function batchTag($sentences)
    {
        $this->setSeparator('/');
        $this->setTagType('ner');

        // Reset errors and output
        $this->setErrors(null);
        $this->setOutput(null);

        // Make temp file to store sentences.
        $tmpfname = tempnam(DIRECTORY_SEPARATOR . 'tmp', 'phpnlptag');
        chmod($tmpfname, 0644);
        $handle = fopen($tmpfname, "w");

        foreach ($sentences as $k => $v) {
            $sentences[$k] = implode(' ', $v);
        }
        $str = implode("\n", $sentences);

        fwrite($handle, $str);
        fclose($handle);

        // Create process to run stanford ner.
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w")   // stderr
        );

        $options = implode(' ', $this->getJavaOptions());
        $osSeparator = $this->php_os == 'windows' ? ';' : ':';
        switch ($this->getTagType()) {
            case 'pos':
                $separator = $this->getSeparator();
                $cmd =
                    $this->getJavaPath()
                    . " $options -cp \""
                    . $this->getJar()
                    . "{$osSeparator}{$this->getLibFolder()}/*\" edu.stanford.nlp.tagger.maxent.MaxentTagger -model "
                    . $this->getModel()
                    . " -textFile "
                    . $tmpfname
                    . " -outputFormat slashTags -tagSeparator "
                    . $separator
                    . " -encoding utf8"
                ;
                break;
            case 'ner':
            default:
                $cmd =
                    $this->getJavaPath()
                    . " $options -cp \""
                    . $this->getJar()
                    . "{$osSeparator}{$this->getLibFolder()}/*\" edu.stanford.nlp.ie.crf.CRFClassifier -loadClassifier "
                    . $this->getClassifier()
                    . " -textFile "
                    . $tmpfname
                    . " -encoding utf8"
                ;
                break;
        }

        $process = proc_open($cmd, $descriptorspec, $pipes, dirname($this->getJar()));

        $output = null;
        $errors = null;
        if (is_resource($process)) {
            // We aren't working with stdin
            fclose($pipes[0]);

            // Get output
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Get any errors
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // close pipe before calling proc_close in order to avoid a deadlock
            $return_value = proc_close($process);
            if ($return_value == -1) {
                throw new Exception("Java process returned with an error (proc_close).");
            }
        }

        unlink($tmpfname);

        if ($errors) {
            $this->setErrors($errors);
        }

        if ($output) {
            $this->setOutput($output);
        }

        return $this->parseOutput();
    }

}