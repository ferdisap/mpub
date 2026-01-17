<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    INFO
    1. Tidak support <__cgmark>

    OUTSTANDING crew
    1. <crewRefCard> belum difungsikan
    2. crewDrill/@drillType belum difungsikan karena belum tau kegunaannya
    3. crewDrill@independentCheck belum difungsikan karena sepengetahuan saya tidak ada gunanya saat di buat pdf
    4. <if> dan <elseIf> tetap di render dalam sequential number jika di pdf (alias fungsinya sama saja).
    5. crewMemberGroup/@actionResponsibility belum difungsikan karena belum tahu kegunaannya 
    6. crewDrillStep/@keepWithNext belum difungsikan karena bingung bagaimana menerapkannya jika ada 'if' or 'elseIf' or 'case'
    7. <thumbTabText> belum difungsikan karena belum tau gunanya untuk apa
   -->

  <xsl:template match="crew">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_security"/>
    <fo:block start-indent="0cm">
      <xsl:call-template name="add_id"/>
      <xsl:apply-templates select="descrCrew"/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="descrCrew">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_warning"/>
    <xsl:call-template name="add_caution"/>
    <xsl:call-template name="add_security"/>
    <xsl:apply-templates/>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="crewDrill">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_warning"/>
    <xsl:call-template name="add_caution"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>

    <fo:block-container margin-bottom="11pt">
      <xsl:call-template name="add_id"/>
      <xsl:if test="@crewStepCondition">
        <xsl:variable name="csc" select="string(@crewStepCondition)"/>
        <fo:block font-size="8pt">
          <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','crewStepCondition', string($csc), 'extension', 'default')" />
        </fo:block>
      </xsl:if>
      <xsl:if test="@skillLevelCode">
        <xsl:variable name="sk" select="string(@skillLevelCode)"/>
        <fo:block font-size="8pt">
          <xsl:text>Skill level: </xsl:text><xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','skillLevelCode', string($sk), 'extension', 'default')" />
        </fo:block>
      </xsl:if>
      <xsl:apply-templates select="title"/>
      <!-- here for <thumbTabText> -->
      <fo:block margin-bottom="11pt">
        <xsl:if test="following-sibling::*">
          <xsl:attribute name="margin-bottom">11pt</xsl:attribute>
        </xsl:if>
        <xsl:apply-templates select="*[name(.) != 'title']"/>
      </fo:block>
    </fo:block-container>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="subCrewDrill">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_warning"/>
    <xsl:call-template name="add_caution"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>

    <fo:block-container margin-bottom="11pt">
      <xsl:call-template name="add_id"/>
      <xsl:if test="@skillLevelCode">
        <xsl:variable name="sk" select="string(@skillLevelCode)"/>
        <fo:block font-size="8pt">
          <xsl:text>Skill level: </xsl:text><xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','skillLevelCode', string($sk), 'extension', 'default')" />
        </fo:block>
      </xsl:if> 
      <fo:block margin-bottom="11pt">
        <xsl:if test="following-sibling::*">
          <xsl:attribute name="margin-bottom">11pt</xsl:attribute>
        </xsl:if>
        <xsl:apply-templates/>
      </fo:block>
    </fo:block-container>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="crewDrillStep">
    <xsl:param name="orderedStepsFlag">
      <xsl:choose>
        <xsl:when test="ancestor-or-self::*/@orderedStepsFlag">
          <xsl:value-of select="string(ancestor-or-self::*/@orderedStepsFlag)"/>
        </xsl:when>
        <xsl:otherwise>1</xsl:otherwise>
      </xsl:choose>
    </xsl:param>
    <xsl:param name="prefixStepFlag">
      <xsl:choose>
        <xsl:when test="$orderedStepsFlag = '1'">
          <xsl:call-template name="crewDrillStepNumber"/>
        </xsl:when>
        <xsl:otherwise>&#x2022;</xsl:otherwise>
      </xsl:choose>
    </xsl:param>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <xsl:call-template name="add_warning"/>
    <xsl:call-template name="add_caution"/>
    <xsl:if test="@crewStepCondition">
      <xsl:variable name="csc" select="string(@crewStepCondition)"/>
      <fo:block font-size="8pt">
        <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','crewStepCondition', string($csc), 'extension', 'default')" />
      </fo:block>
    </xsl:if>
    <xsl:if test="@skillLevelCode">
      <xsl:variable name="sk" select="string(@skillLevelCode)"/>
      <fo:block font-size="8pt">
        <xsl:text>Skill level: </xsl:text><xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','skillLevelCode', string($sk), 'extension', 'default')" />
      </fo:block>
    </xsl:if>
    <xsl:if test="title">
      <xsl:apply-templates select="title"/>
    </xsl:if>
    <xsl:apply-templates select="crewMemberGroup"/>
    
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:if test="@memorizeStepsFlag">
        <xsl:attribute name="font-weight">bold</xsl:attribute>
      </xsl:if>
      <fo:table>
        <fo:table-body>
          <fo:table-row>
            <fo:table-cell width="1cm">
              <fo:block>
                <xsl:value-of select="$prefixStepFlag"/>
              </fo:block>
            </fo:table-cell>
            <fo:table-cell width="14cm">
              <fo:block>
                <xsl:apply-templates select="crewProcedureName|challengeAndResponse|crewDrillStep|case|if|elseIf|warning|caution|note|para|figure|figureAlts|multimedia|multimediaAlts|foldout|table|caption"/>
              </fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-body>
      </fo:table>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>  

  <xsl:template match="crewProcedureName">
    <xsl:variable name="challengeWidth" select="number(14)"/>
    <xsl:variable name="qtyAncestor" select="count(ancestor::crewDrillStep)"/>
    <fo:table>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <xsl:attribute name="width"><xsl:value-of select="$challengeWidth - $qtyAncestor"/>cm</xsl:attribute>
            <fo:block>
              <xsl:call-template name="add_applicability"/>
              <xsl:call-template name="add_controlAuthority"/>
              <fo:inline-container width="10cm">
                <fo:block>
                  <xsl:apply-templates/>
                </fo:block>
              </fo:inline-container>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>

  <xsl:template match="challengeAndResponse">
    <xsl:variable name="challengeWidth" select="number(10)"/>
    <xsl:variable name="qtyAncestor" select="count(ancestor::crewDrillStep)"/>
    <fo:table>
      <fo:table-body>
        <fo:table-row>
          <fo:table-cell>
            <xsl:attribute name="width"><xsl:value-of select="$challengeWidth - $qtyAncestor"/>cm</xsl:attribute>
            <fo:block margin-right="1pt">
              <xsl:apply-templates select="challenge"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell width="3cm" display-align="before" margin-left="">
            <fo:block margin-left="1pt">
              <xsl:apply-templates select="response"/>
            </fo:block>
          </fo:table-cell>
          <fo:table-cell width="1cm" display-align="after" text-align="right">
            <fo:block>
              <xsl:apply-templates select="crewMemberGroup"/>
            </fo:block>
          </fo:table-cell>
        </fo:table-row>
      </fo:table-body>
    </fo:table>
  </xsl:template>

  <xsl:template match="if|elseIf|case">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block margin-top="6pt" margin-bottom="6pt">
      <xsl:call-template name="add_id"/>
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>
  
  <xsl:template match="caseCond">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_security"/>
    <fo:block>
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="challenge">
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <fo:block>
      <xsl:apply-templates/>
      <xsl:variable name="separatorStyle">
        <xsl:choose>
          <xsl:when test="string(@separatorStyle) = 'line'">line</xsl:when>
          <xsl:when test="string(@separatorStyle) = 'none'">space</xsl:when>
          <xsl:otherwise>dots</xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <fo:leader leader-length.optimum="100%" leader-pattern="{$separatorStyle}"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="response">
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority" />
    <fo:block>
      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>

  <xsl:template match="crewMemberGroup[parent::challengeAndResponse]">
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:for-each select="crewMember">
      <xsl:variable name="cmtype" select="string(@crewMemberType)"/>
      <fo:block>
        <xsl:call-template name="add_applicability"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','crewMemberType', string($cmType), 'abbreviation', 'extension', 'default')" />
      </fo:block>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="crewMemberGroup[parent::crewDrillStep]">
    <fo:table-cell width="14cm">      
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:for-each select="crewMember">
        <xsl:variable name="cmtype" select="string(@crewMemberType)"/>
        <fo:block>
          <xsl:call-template name="add_applicability"/>
          <xsl:call-template name="add_controlAuthority"/>
          <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','crewMemberType', string($cmType), 'abbreviation', 'extension', 'default')" />
        </fo:block>
      </xsl:for-each>
    </fo:table-cell>
  </xsl:template>

  <xsl:template match="endMatter">
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <fo:block>
      <xsl:call-template name="style-para"/>
      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>

  <!-- dipanggil di internalRef juga -->
  <xsl:template name="crewDrillStepNumber">    
    <xsl:value-of select="
    count(preceding-sibling::crewDrillStep)+
    count(preceding-sibling::if/crewDrillStep)+
    count(preceding-sibling::elseIf/crewDrillStep)+
    count(parent::if/preceding-sibling::crewDrillStep)+
    count(parent::if/preceding-sibling::if/crewDrillStep)+
    count(parent::if/preceding-sibling::elseIf/crewDrillStep)+
    count(parent::elseIf/preceding-sibling::crewDrillStep)+
    count(parent::elseIf/preceding-sibling::if/crewDrillStep)+
    count(parent::elseIf/preceding-sibling::elseIf/crewDrillStep)+
    1"/>
    <!-- <xsl:choose>
      <xsl:when test="parent::__cgmark">
        <xsl:number level="any" from="crewDrill|subCrewDrill" count="__cgmark|crewDrillStep"/>
        <xsl:number level="multiple" from="crewDrill|subCrewDrill" count="__cgmark"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:number level="multiple" from="crewDrill|subCrewDrill" count="crewDrillStep|if|elseIf|__cgmark[child::crewDrillStep]|__cgmark[child::if]|__cgmark[child::elseIf]"/>
      </xsl:otherwise>
    </xsl:choose> -->
  </xsl:template>
</xsl:transform>