<el-form-item :label="t('{LCASE_COLUMN_NAME}')" {PROP}>
    <el-checkbox-group v-model="formData.{COLUMN_NAME}" :placeholder="t('{LCASE_COLUMN_NAME}Placeholder')">
        <el-checkbox  label="1">选项1</el-checkbox>
        <el-checkbox  label="2">选项2</el-checkbox>
    </el-checkbox-group>
</el-form-item>
