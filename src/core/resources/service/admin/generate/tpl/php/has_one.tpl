    /**
     * @return HasOne
     */
    public function <{relationName}>()
    {
        return $this->hasOne(<{relationModel}>Model::class, '<{foreignKey}>', '<{localKey}>');
    }
