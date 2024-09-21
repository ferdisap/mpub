<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">


  <!-- Note
    1. selanjutnya tes untuk numbering pakai <xsl:number/>, eg: <xsl:number level="multiple" count="levelledPara"/>, <xsl:number level="multiple" count="crewDrill|if"/> (output di crew.xsd '1.1')
  -->

  <!-- general style title fot default font size 11pt -->
  <xsl:template name="style-title">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="level"/>
    <xsl:param name="length_unit">
      <xsl:call-template name="get_layout_unit_length"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="stIndent">
      <xsl:call-template name="get_stIndent"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$length_unit"/>
    </xsl:param>

    <xsl:variable name="fontSize">
      <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$area_unit"/>
    </xsl:variable>
    
    <xsl:choose>
      <xsl:when test="$fontSize = '10pt'">
        <xsl:call-template name="style-title-default-fontSize10pt">
          <xsl:with-param name="masterName" select="$masterName"/>
          <xsl:with-param name="level" select="$level"/>
          <xsl:with-param name="length_unit" select="$length_unit"/>
          <xsl:with-param name="area_unit" select="$area_unit"/>
          <xsl:with-param name="stIndent" select="$stIndent"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:variable name="titleNumberWidth">
          <xsl:choose>
            <xsl:when test="boolean($stIndent) or $stIndent != ''">
              <xsl:text>0</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>1.5</xsl:text>
              <xsl:value-of select="$length_unit"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:variable>
        <xsl:choose>
          <!-- compliance to S1000D v5.0 chap 6.2.2 page 8, para 2.6.1 par2-->
          <xsl:when test="parent::randomList or parent::sequentialList or parent::definitionList or parent::attentionSequentialList or parent::attentionRandomList">
            <xsl:attribute name="font-weight">bold</xsl:attribute>
            <xsl:attribute name="margin-bottom">4<xsl:value-of select="$area_unit"/></xsl:attribute> <!-- leading 14pt setara 4pt karena fontsize 10pt -->
          </xsl:when>
          <!-- compliance to S1000D v5.0 chap 6.2.2 page 5, table 2 dan table 3 (leading to a follow-on text paragraph)  -->
          <xsl:when test="$level = 'c1'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">14<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="font-weight">bold</xsl:attribute>
              <!-- <xsl:attribute name="margin-bottom">17<xsl:value-of select="$area_unit"/></xsl:attribute> -->
              <xsl:attribute name="margin-bottom">7<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="text-align">center</xsl:attribute>
            </xsl:if>
          </xsl:when>
          <xsl:when test="$level = 'c2'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">14<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="font-weight">bold</xsl:attribute>
              <xsl:attribute name="font-style">italic</xsl:attribute>
              <!-- <xsl:attribute name="margin-bottom">17<xsl:value-of select="$area_unit"/></xsl:attribute> -->
              <xsl:attribute name="margin-bottom">7<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="text-align">center</xsl:attribute>
            </xsl:if>
          </xsl:when>
          <xsl:when test="$level = 's0'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">14<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="font-weight">bold</xsl:attribute>
              <xsl:attribute name="margin-bottom">7<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="text-align">left</xsl:attribute>
            </xsl:if>
          </xsl:when>
          <xsl:when test="$level = 's1'">
            <xsl:attribute name="font-size">14<xsl:value-of select="$area_unit"/></xsl:attribute>
            <xsl:attribute name="font-weight">bold</xsl:attribute>
            <xsl:attribute name="margin-bottom">5<xsl:value-of select="$area_unit"/></xsl:attribute>
            <xsl:attribute name="text-align">left</xsl:attribute>
            <!-- <xsl:call-template name="numbered"/> -->
            <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
              <fo:block>
                <!-- <xsl:call-template name="numbered"/> -->
                <xsl:number level="multiple" count="levelledPara"/>
              </fo:block>
            </fo:inline-container>
          </xsl:when>
          <xsl:when test="$level = 's2'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">12<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="font-weight">bold</xsl:attribute>
              <xsl:attribute name="margin-bottom">2<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="text-align">left</xsl:attribute>
            </xsl:if>
            <!-- <xsl:call-template name="numbered"/> -->
            <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
              <fo:block>
                <!-- <xsl:call-template name="numbered"/> -->
                <xsl:number level="multiple" count="levelledPara"/>
              </fo:block>
            </fo:inline-container>
          </xsl:when>
          <xsl:when test="$level = 's3'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="font-weight">bold</xsl:attribute>
              <xsl:attribute name="text-align">left</xsl:attribute>
              <xsl:attribute name="margin-bottom">1<xsl:value-of select="$area_unit"/></xsl:attribute>
            </xsl:if>
            <!-- <xsl:call-template name="numbered"/> -->
            <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
              <fo:block>
                <!-- <xsl:call-template name="numbered"/> -->
                <xsl:number level="multiple" count="levelledPara"/>
              </fo:block>
            </fo:inline-container>
          </xsl:when>
          <xsl:when test="$level = 's4'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="text-align">left</xsl:attribute>
              <xsl:attribute name="margin-bottom">1<xsl:value-of select="$area_unit"/></xsl:attribute>
            </xsl:if>
            <!-- <xsl:call-template name="numbered"/> -->
            <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
              <fo:block>
                <!-- <xsl:call-template name="numbered"/> -->
                <xsl:number level="multiple" count="levelledPara"/>
              </fo:block>
            </fo:inline-container>
          </xsl:when>
          <xsl:when test="$level = 's5'">
            <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
              <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
              <xsl:attribute name="text-align">left</xsl:attribute>
              <xsl:attribute name="font-style">italic</xsl:attribute>
              <xsl:attribute name="margin-bottom">1<xsl:value-of select="$area_unit"/></xsl:attribute>
            </xsl:if>
            <!-- <xsl:call-template name="numbered"/> -->
            <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
              <fo:block>
                <!-- <xsl:call-template name="numbered"/> -->
                <xsl:number level="multiple" count="levelledPara"/>
              </fo:block>
            </fo:inline-container>
          </xsl:when>
          <xsl:otherwise>
            <!-- <xsl:value-of select="php:function('dd',string(.))"/> -->
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>

  </xsl:template>

  <!-- style title for  default font size 10pt -->
  <xsl:template name="style-title-default-fontSize10pt">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:param name="level"/>
    <xsl:param name="length_unit">
      <xsl:call-template name="get_layout_unit_length"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="stIndent">
      <xsl:call-template name="get_stIndent"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$length_unit"/>
    </xsl:param>
    <xsl:variable name="titleNumberWidth">
      <xsl:choose>
        <xsl:when test="boolean($stIndent) or $stIndent != ''">
          <xsl:text>0</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>1.3</xsl:text>
          <xsl:value-of select="$length_unit"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <!-- compliance to S1000D v5.0 chap 6.2.2 page 8, para 2.6.1 par2-->
      <xsl:when test="parent::randomList or parent::sequentialList or parent::definitionList or parent::attentionSequentialList or parent::attentionRandomList">
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="margin-bottom">3<xsl:value-of select="$area_unit"/></xsl:attribute> <!-- leading 14pt setara 4pt karena fontsize 10pt -->
      </xsl:when>
      <!-- compliance to S1000D v5.0 chap 6.2.2 page 5, table 2 dan table 3 (leading to a follow-on text paragraph)  -->
      <xsl:when test="$level = 'c1'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">12<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="font-weight">bold</xsl:attribute>
          <!-- <xsl:attribute name="margin-bottom">17<xsl:value-of select="$area_unit"/></xsl:attribute> -->
          <xsl:attribute name="margin-bottom">6<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="text-align">center</xsl:attribute>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$level = 'c2'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">12<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="font-weight">bold</xsl:attribute>
          <xsl:attribute name="font-style">italic</xsl:attribute>
          <!-- <xsl:attribute name="margin-bottom">17<xsl:value-of select="$area_unit"/></xsl:attribute> -->
          <xsl:attribute name="margin-bottom">6<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="text-align">center</xsl:attribute>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$level = 's0'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">12<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="font-weight">bold</xsl:attribute>
          <xsl:attribute name="margin-bottom">6<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="text-align">left</xsl:attribute>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$level = 's1'">
        <xsl:attribute name="font-size">12<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="margin-bottom">4<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="text-align">left</xsl:attribute>
        <!-- <xsl:call-template name="numbered"/> -->
        <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
          <fo:block>
            <!-- <xsl:call-template name="numbered"/> -->
            <xsl:number level="multiple" count="levelledPara"/>
          </fo:block>
        </fo:inline-container>
      </xsl:when>
      <xsl:when test="$level = 's2'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="font-weight">bold</xsl:attribute>
          <xsl:attribute name="text-align">left</xsl:attribute>
          <xsl:attribute name="margin-bottom">2<xsl:value-of select="$area_unit"/></xsl:attribute>
        </xsl:if>
        <!-- <xsl:call-template name="numbered"/> -->
        <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
          <fo:block>
            <!-- <xsl:call-template name="numbered"/> -->
            <xsl:number level="multiple" count="levelledPara"/>
          </fo:block>
        </fo:inline-container>
      </xsl:when>
      <xsl:when test="$level = 's3'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="font-weight">bold</xsl:attribute>
          <xsl:attribute name="text-align">left</xsl:attribute>
          <xsl:attribute name="margin-bottom">1<xsl:value-of select="$area_unit"/></xsl:attribute>
        </xsl:if>
        <!-- <xsl:call-template name="numbered"/> -->
        <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
          <fo:block>
            <!-- <xsl:call-template name="numbered"/> -->
            <xsl:number level="multiple" count="levelledPara"/>
          </fo:block>
        </fo:inline-container>
      </xsl:when>
      <xsl:when test="$level = 's4'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="text-align">left</xsl:attribute>
          <xsl:attribute name="margin-bottom">1<xsl:value-of select="$area_unit"/></xsl:attribute>
        </xsl:if>
        <!-- <xsl:call-template name="numbered"/> -->
        <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
          <fo:block>
            <!-- <xsl:call-template name="numbered"/> -->
            <xsl:number level="multiple" count="levelledPara"/>
          </fo:block>
        </fo:inline-container>
      </xsl:when>
      <xsl:when test="$level = 's5'">
        <xsl:if test="following-sibling::para or following-sibling::figure or following-sibling::table or following-sibling::levelledPara">
          <xsl:attribute name="font-size">10<xsl:value-of select="$area_unit"/></xsl:attribute>
          <xsl:attribute name="text-align">left</xsl:attribute>
          <xsl:attribute name="font-style">italic</xsl:attribute>
          <xsl:attribute name="margin-bottom">1<xsl:value-of select="$area_unit"/></xsl:attribute>
        </xsl:if>
        <!-- <xsl:call-template name="numbered"/> -->
        <fo:inline-container start-indent="-{$stIndent}" width="{$titleNumberWidth}">
          <fo:block>
            <!-- <xsl:call-template name="numbered"/> -->
            <xsl:number level="multiple" count="levelledPara"/>
          </fo:block>
        </fo:inline-container>
      </xsl:when>
      <xsl:otherwise>
        <!-- <xsl:value-of select="php:function('dd',string(.))"/> -->
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:transform>