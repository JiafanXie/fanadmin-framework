    /**
     * @return HasMany
     */
    public function <{relationName}>()
    {
        return $this->hasMany(<{relationModel}>Model::class, '<{foreignKey}>', '<{localKey}>');
    }
